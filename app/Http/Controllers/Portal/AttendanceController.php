<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\MeetingAttendance;
use App\Models\MeetingAttendanceSummary;
use App\Exports\AttendanceTemplateExport;
use App\Imports\AttendanceImport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $department = Department::findOrFail($departmentId);
        $this->authorizeFeature('attendance');

        
        // Let's get generic portal info for layout
        $availableDepartments = $this->getAvailableDepartments();

        // Filters
        $month  = $request->input('month', now()->month);
        $year   = $request->input('year', now()->year);
        $type   = $request->input('type', 'church');   // default to church
        $search = $request->input('search', '');

        // Get Meetings relevant to this department
        $meetings = Meeting::where(function($query) use ($departmentId) {
                $query->where('department_id', $departmentId)
                      ->orWhereNull('department_id');
            })
            ->when($month, fn($q) => $q->whereMonth('date', $month))
            ->when($year,  fn($q) => $q->whereYear('date', $year))
            ->when($type,  fn($q) => $q->where('type', $type))
            ->when($search, fn($q) => $q->where(function($sq) use ($search) {
                $sq->where('topic', 'like', "%{$search}%")
                   ->orWhere('memory_verse', 'like', "%{$search}%")
                   ->orWhere('scripture', 'like', "%{$search}%");
            }))
            ->with(['attendanceSummaries' => fn($q) => $q->where('department_id', $departmentId)->select('meeting_id', 'department_id', 'manual_count', 'memory_verse_count')])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->paginate(15)
            ->withQueryString()
            ->through(function ($meeting) {
                $summary = $meeting->attendanceSummaries->first();
                $data = $meeting->toArray();
                unset($data['attendance_summaries']); // remove nested from toArray
                $data['attendance_summary'] = $summary ? [
                    'manual_count'       => $summary->manual_count,
                    'memory_verse_count' => $summary->memory_verse_count,
                ] : null;
                return $data;
            });

        return Inertia::render('Portal/Attendance/Index', [
            'department'           => $department,
            'departments'          => Department::select('id', 'name')->get(),
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin'        => auth()->user()->isSuperAdmin(),
            'meetings'             => $meetings,
            'filters'              => [
                'month'  => $month,
                'year'   => $year,
                'type'   => $type,
                'search' => $search,
            ],
        ]);

    }

    public function show(Request $request, Meeting $meeting)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $department = Department::findOrFail($departmentId);
        $this->authorizeFeature('attendance');


        $availableDepartments = $this->getAvailableDepartments();

        // 1. Get Summary for this department
        $summary = MeetingAttendanceSummary::firstOrNew([
            'meeting_id' => $meeting->id,
            'department_id' => $department->id,
        ], [
            'manual_count' => 0,
            'memory_verse_count' => 0,
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
            'isGlobalAdmin' => auth()->user()->isSuperAdmin(),
            'meeting' => $meeting,
            'summary' => $summary,
            'members' => $memberList,
        ]);
    }

    public function store(Request $request, Meeting $meeting)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) return redirect()->route('portal.index');
        
        $this->authorizeManage('attendance');


        $validated = $request->validate([
            'manual_count' => 'required|integer|min:0',
            'memory_verse_count' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:255',
            'attendances' => 'required|array',
            'attendances.*.id' => 'required|exists:members,id',
            'attendances.*.status' => 'required|in:present,absent,excused',
            'attendances.*.memorized_verse' => 'nullable|boolean',
            'attendances.*.quiz_score' => 'nullable|integer|min:0',
        ]);

        \DB::transaction(function () use ($meeting, $departmentId, $validated) {
            // Count memorized_verse from named attendances
            $autoVerseCount = collect($validated['attendances'])
                ->where('status', 'present')
                ->where('memorized_verse', true)
                ->count();
            // Use explicit value if provided (manual mode), otherwise use auto-count from named
            $verseCount = isset($validated['memory_verse_count']) && $validated['memory_verse_count'] !== null
                ? (int) $validated['memory_verse_count']
                : $autoVerseCount;

            // Update Summary
            MeetingAttendanceSummary::updateOrCreate(
                ['meeting_id' => $meeting->id, 'department_id' => $departmentId],
                [
                    'manual_count' => $validated['manual_count'],
                    'memory_verse_count' => $verseCount,
                    'notes' => $validated['notes'],
                ]
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

    /**
     * Export attendance template for a specific meeting to Excel.
     */
    public function exportTemplate(Meeting $meeting)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $this->authorizeFeature('attendance');


        $deptName = Department::find($departmentId)?->name ?? 'ban-nganh';
        $dateStr  = is_string($meeting->date) ? $meeting->date : $meeting->date->format('Y-m-d');
        $filename = "diem-danh_{$meeting->id}_{$dateStr}.xlsx";

        return Excel::download(
            new AttendanceTemplateExport($meeting, $departmentId),
            $filename
        );
    }

    /**
     * Import attendance from Excel file.
     */
    public function import(Request $request)
    {
        $departmentId = session('active_portal_dept_id');
        if (!$departmentId) {
            return redirect()->route('portal.index');
        }

        $request->validate([
            'file'       => 'required|file|mimes:xlsx,xls|max:5120',
            'meeting_id' => 'required|exists:meetings,id',
        ]);

        $meeting = Meeting::findOrFail($request->meeting_id);
        $this->authorizeManage('attendance');


        try {
            $import = new AttendanceImport($meeting->id, $departmentId);
            Excel::import($import, $request->file('file'));

            $msg = "Import thành công {$import->importedCount} thành viên.";
            if (!empty($import->errors)) {
                $msg .= ' Bỏ qua ' . $import->skippedCount . ' dòng lỗi.';
            }

            return back()->with('success', $msg)->with('import_errors', $import->errors);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Lỗi import: ' . $e->getMessage()]);
        }
    }
}

