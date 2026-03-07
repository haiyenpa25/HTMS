<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\MeetingAttendanceSummary;
use Illuminate\Support\Facades\Gate;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $department = Department::findOrFail($departmentId);
        Gate::authorize('access_portal', [Department::class, $department]);
        
        // Let's get generic portal info for layout
        $availableDepartments = $this->getAvailableDepartments();

        // Filters
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        // Get Meetings relevant to this department (either Church wide or specific to this department)
        $meetings = Meeting::where(function($query) use ($departmentId) {
                $query->where('department_id', $departmentId)
                      ->orWhereNull('department_id');
            })
            ->when($month, function($query, $month) {
                $query->whereMonth('date', $month);
            })
            ->when($year, function($query, $year) {
                $query->whereYear('date', $year);
            })
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Portal/Attendance/Index', [
            'department' => $department,
            'departments' => Department::select('id', 'name')->get(),
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin' => auth()->user()->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin']),
            'meetings' => $meetings,
            'filters' => [
                'month' => $month,
                'year' => $year
            ]
        ]);
    }

    public function show(Request $request, Meeting $meeting)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $department = Department::findOrFail($departmentId);
        Gate::authorize('view_attendance', [Meeting::class, $meeting]);

        $availableDepartments = $this->getAvailableDepartments();

        // 1. Get Summary for this department
        $summary = MeetingAttendanceSummary::firstOrNew([
            'meeting_id' => $meeting->id,
            'department_id' => $department->id,
        ], [
            'manual_count' => 0,
            'notes' => ''
        ]);

        // 2. Get all members of this department and their team within this department
        $members = $department->members()->orderBy('full_name')->get();
        $members->load(['teams' => function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        }]);

        // 3. Get existing Individual Attendances for these members
        $memberIds = $members->pluck('id');
        $attendances = MeetingAttendance::where('meeting_id', $meeting->id)
            ->whereIn('member_id', $memberIds)
            ->get()
            ->keyBy('member_id');

        // Transform members to include attendance status and team_id
        $memberList = $members->map(function ($member) use ($attendances) {
            $att = $attendances->get($member->id);
            $teamId = $member->teams->first()->id ?? null;
            return [
                'id' => $member->id,
                'full_name' => $member->full_name,
                'phone' => $member->phone,
                'team_id' => $teamId,
                'status' => $att ? $att->status : 'absent', // default to absent if not marked yet
                'attendance_id' => $att ? $att->id : null,
                'memorized_verse' => $att ? (bool)$att->memorized_verse : false,
                'quiz_score' => $att ? $att->quiz_score : null,
            ];
        });
        
        $teams = $department->teams()->select('id', 'name')->get();

        return Inertia::render('Portal/Attendance/Show', [
            'department' => $department,
            'teams' => $teams,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin' => auth()->user()->hasRole(['Pastor', 'BTS_Admin', 'Super_Admin']),
            'meeting' => $meeting,
            'summary' => $summary,
            'members' => $memberList,
        ]);
    }

    public function store(Request $request, Meeting $meeting)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) return redirect()->route('portal.index');
        
        Gate::authorize('mark_attendance', [Meeting::class, $meeting]);

        $validated = $request->validate([
            'manual_count' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
            'attendances' => 'required|array',
            'attendances.*.id' => 'required|exists:members,id',
            'attendances.*.status' => 'required|in:present,absent,excused',
            'attendances.*.memorized_verse' => 'nullable|boolean',
            'attendances.*.quiz_score' => 'nullable|integer|min:0',
        ]);

        \DB::transaction(function () use ($meeting, $departmentId, $validated) {
            // Update Summary
            MeetingAttendanceSummary::updateOrCreate(
                ['meeting_id' => $meeting->id, 'department_id' => $departmentId],
                ['manual_count' => $validated['manual_count'], 'notes' => $validated['notes']]
            );

            // Update Individual Check-ins
            foreach ($validated['attendances'] as $att) {
                MeetingAttendance::updateOrCreate(
                    ['meeting_id' => $meeting->id, 'member_id' => $att['id']],
                    [
                        'status' => $att['status'],
                        'memorized_verse' => $att['memorized_verse'] ?? false,
                        'quiz_score' => $att['quiz_score'] ?? null,
                    ]
                );
            }
        });

        return back()->with('success', 'Đã lưu điểm danh thành công!');
    }

    private function getAvailableDepartments()
    {
        return app(\App\Services\PortalService::class)->getAvailableDepartments(auth()->user(), 'activities');
    }
}

