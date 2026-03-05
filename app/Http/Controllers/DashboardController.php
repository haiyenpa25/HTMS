<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Member;
use App\Models\MemberSensitive;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\MeetingAttendanceSummary;
use App\Models\EduClass;
use App\Models\EduSession;
use App\Models\Visitation;
use App\Models\DepartmentReport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Only accessible by Pastor / Super Admin
        if (!$user->hasRole(['Pastor', 'Super_Admin'])) {
            return redirect()->route('portal.index');
        }

        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $today = Carbon::today();
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd   = $today->copy()->endOfWeek();

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // ── 1. PENDING REPORTS ─────────────────────────────────────
        $pendingReports = DepartmentReport::where('status', 'submitted')
            ->with('department')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id'          => $r->id,
                'dept_name'   => $r->department->name ?? '—',
                'month'       => $r->report_month,
                'year'        => $r->report_year,
                'submitted_at'=> $r->updated_at->format('d/m/Y'),
            ]);

        // ── 2. MEETING TABLE (all dept + church this month) ────────
        $meetings = Meeting::whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with(['speaker', 'attendanceSummaries', 'department'])
            ->orderBy('date')
            ->get()
            ->map(function($m) {
                $totalAttendance = $m->attendanceSummaries->sum('manual_count');
                return [
                    'id'          => $m->id,
                    'date'        => Carbon::parse($m->date)->format('d/m/Y'),
                    'day'         => Carbon::parse($m->date)->locale('vi')->isoFormat('dddd'),
                    'type'        => $m->type,
                    'dept_name'   => $m->department?->name ?? 'Hội Thánh',
                    'topic'       => $m->topic ?? '',
                    'scripture'   => $m->scripture ?? '',
                    'memory_verse'=> $m->memory_verse ?? '',
                    'speaker'     => $m->speaker?->name ?? $m->preacher ?? '',
                    'attendance'  => $totalAttendance,
                ];
            });

        // ── 3. ATTENDANCE CHART — per dept per week this month ─────
        $depts = Department::where('block', 'activities')->select('id', 'name')->get();

        $deptAttLines = $depts->map(function($dept) use ($monthStart, $monthEnd) {
            // Get dept meetings this month with attendance
            $mtgs = Meeting::where('type', 'department')
                ->where('department_id', $dept->id)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->with('attendanceSummaries')
                ->orderBy('date')
                ->get();

            // Group by week_no (1-5)
            $weekData = array_fill(1, 5, 0);
            foreach ($mtgs as $m) {
                $weekNo = (int) ceil(Carbon::parse($m->date)->day / 7);
                $weekNo = min($weekNo, 5);
                $weekData[$weekNo] += $m->attendanceSummaries->sum('manual_count');
            }
            return [
                'name' => $dept->name,
                'data' => array_values($weekData),
            ];
        });

        // Church meeting attendance line
        $churchMtgs = Meeting::where('type', 'church')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with('attendanceSummaries')
            ->orderBy('date')
            ->get();

        $churchWeekData = array_fill(1, 5, 0);
        foreach ($churchMtgs as $m) {
            $weekNo = min((int) ceil(Carbon::parse($m->date)->day / 7), 5);
            $churchWeekData[$weekNo] += $m->attendanceSummaries->sum('manual_count');
        }
        $churchLine = ['name' => 'Hội Thánh', 'data' => array_values($churchWeekData)];

        // ── 4. CGDG — 3 loại lớp ─────────────────────────────────
        $cgdgTypes = ['sunday_school' => 'Trường Chủ Nhật', 'gospel' => 'Giáo Lý', 'bible_quiz' => 'Kinh Thánh Trắc Nghiệm'];

        $cgdgData = [];
        foreach ($cgdgTypes as $typeKey => $typeLabel) {
            $classes = EduClass::where('class_type', $typeKey)
                ->with(['sessions' => function($q) use ($monthStart, $monthEnd) {
                    $q->whereBetween('session_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                      ->orderBy('session_date');
                }])
                ->get();

        $rows = $classes->map(function($cls) use ($typeKey) {
                $sessions = $cls->sessions;

                // With eager-loaded EduSessionRecords for bible_quiz
                $sessionRows = $sessions->map(function($s) use ($typeKey) {
                    $row = [
                        'date'       => $s->session_date->format('d/m'),
                        'attendance' => $s->total_present ?? 0,
                        'topic'      => $s->topic ?? '',
                    ];
                    if ($typeKey === 'bible_quiz') {
                        // Count scored (quiz_score NOT NULL) từ records
                        $records = \App\Models\EduSessionRecord::where('edu_session_id', $s->id)
                            ->whereNotNull('quiz_score')
                            ->pluck('quiz_score');
                        $row['scored_count'] = $records->count();
                        $row['avg_score']    = $records->count() > 0 ? round($records->average(), 1) : null;
                    }
                    return $row;
                })->values();

                if ($typeKey === 'bible_quiz') {
                    $allScores = $sessions->flatMap(function($s) {
                        return \App\Models\EduSessionRecord::where('edu_session_id', $s->id)
                            ->whereNotNull('quiz_score')
                            ->pluck('quiz_score');
                    });
                    return [
                        'class_id'     => $cls->id,
                        'class_name'   => $cls->name,
                        'sessions'     => $sessionRows,
                        'total'        => $sessions->sum('total_present'),
                        'scored_total' => $sessions->sum(function($s) {
                            return \App\Models\EduSessionRecord::where('edu_session_id', $s->id)
                                ->whereNotNull('quiz_score')->count();
                        }),
                        'avg_score_all'=> $allScores->count() > 0 ? round($allScores->average(), 1) : null,
                        'chart_data'   => $sessionRows->pluck('scored_count')->values(),
                        'chart_dates'  => $sessionRows->pluck('date')->values(),
                    ];
                }

                return [
                    'class_id'    => $cls->id,
                    'class_name'  => $cls->name,
                    'sessions'    => $sessionRows,
                    'total'       => $sessions->sum('total_present'),
                    'chart_data'  => $sessions->map(fn($s) => $s->total_present ?? 0)->values(),
                    'chart_dates' => $sessions->map(fn($s) => $s->session_date->format('d/m'))->values(),
                ];
            });

            $cgdgData[$typeKey] = [
                'label'   => $typeLabel,
                'classes' => $rows->values(),
            ];
        }

        // ── 5. SINH NHẬT THÁNG NÀY ───────────────────────────────
        $birthdayMembers = Member::whereNotNull('date_of_birth')
            ->whereMonth('date_of_birth', $month)
            ->get(['id', 'full_name', 'date_of_birth', 'gender', 'phone'])
            ->sortBy(fn($m) => $m->date_of_birth->day)
            ->map(function($m) use ($year, $today, $weekStart, $weekEnd) {
                $bday = Carbon::create($year, $m->date_of_birth->month, $m->date_of_birth->day);
                $isThisWeek = $bday->between($weekStart, $weekEnd);
                $isToday    = $bday->isSameDay($today);
                return [
                    'id'          => $m->id,
                    'full_name'   => $m->full_name,
                    'birth_day'   => $m->date_of_birth->format('d/m'),
                    'age'         => $year - $m->date_of_birth->year,
                    'gender'      => $m->gender,
                    'phone'       => $m->phone,
                    'is_today'    => $isToday,
                    'is_this_week'=> $isThisWeek,
                ];
            });


        // ── 6. THĂM VIẾNG THÁNG NÀY ──────────────────────────────
        $visitations = Visitation::whereBetween('visit_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with(['member', 'department', 'visitors'])
            ->orderBy('visit_date', 'desc')
            ->get()
            ->map(fn($v) => [
                'id'           => $v->id,
                'visit_date'   => $v->visit_date->format('d/m/Y'),
                'member_name'  => $v->member?->full_name ?? '—',
                'dept_name'    => $v->department?->name ?? 'Chung',
                'reason'       => $v->reason ?? '',
                'status'       => $v->status ?? 'planned',
                'priority'     => $v->priority ?? 'normal',
                'visitors'     => $v->visitors->map(fn($vis) => $vis->full_name)->join(', '),
            ]);

        $visitationStats = [
            'total'     => $visitations->count(),
            'completed' => $visitations->where('status', 'completed')->count(),
            'planned'   => $visitations->where('status', 'planned')->count(),
        ];

        // ── 7. TÍN HỮU MỚI (30 ngày và 90 ngày) ─────────────────
        $newMembers30 = Member::whereNotNull('faith_date')
            ->where('faith_date', '>=', $today->copy()->subDays(30))
            ->orderBy('faith_date', 'desc')
            ->get(['id', 'full_name', 'faith_date', 'phone', 'gender'])
            ->map(fn($m) => [
                'id'         => $m->id,
                'full_name'  => $m->full_name,
                'faith_date' => $m->faith_date->format('d/m/Y'),
                'phone'      => $m->phone,
                'gender'     => $m->gender,
            ]);

        $newMembers90 = Member::whereNotNull('faith_date')
            ->where('faith_date', '>=', $today->copy()->subDays(90)->toDateString())
            ->where('faith_date', '<', $today->copy()->subDays(30)->toDateString())
            ->orderBy('faith_date', 'desc')
            ->get(['id', 'full_name', 'faith_date', 'phone', 'gender'])
            ->map(fn($m) => [
                'id'         => $m->id,
                'full_name'  => $m->full_name,
                'faith_date' => $m->faith_date->format('d/m/Y'),
                'phone'      => $m->phone,
                'gender'     => $m->gender,
            ]);

        // ── 8. NGÀY ĐẶC BIỆT THÁNG NÀY ─────────────────────────
        $specialDates = [];

        // Báp tem trong tháng
        $baptisms = Member::whereNotNull('baptism_date')
            ->whereMonth('baptism_date', $month)
            ->get(['id', 'full_name', 'baptism_date'])
            ->map(fn($m) => [
                'id'        => $m->id,
                'full_name' => $m->full_name,
                'date'      => $m->baptism_date->format('d/m'),
                'type'      => 'baptism',
                'label'     => 'Báp tem',
                'years'     => $year - $m->baptism_date->year,
            ]);

        // Gia nhập trong tháng
        $joinings = Member::whereNotNull('joined_date')
            ->whereMonth('joined_date', $month)
            ->get(['id', 'full_name', 'joined_date'])
            ->map(fn($m) => [
                'id'        => $m->id,
                'full_name' => $m->full_name,
                'date'      => $m->joined_date->format('d/m'),
                'type'      => 'joined',
                'label'     => 'Gia nhập HT',
                'years'     => $year - $m->joined_date->year,
            ]);

        // Kết hôn — bỏ qua vì MemberSensitive chưa có wedding_date
        // Có thể thêm sau khi migration bổ sung field này

        $specialDates = collect($baptisms)->merge($joinings)
            ->sortBy('date')->values();


        // ── 9. KPI TỔNG QUAN ─────────────────────────────────────
        $totalMembers   = Member::count();
        $activeMembers  = Member::where('status', 'Chính thức')->count();
        $newThisMonth   = Member::whereMonth('created_at', $month)->whereYear('created_at', $year)->count();

        return Inertia::render('Dashboard', [
            'filters' => ['month' => $month, 'year' => $year],

            // KPI
            'kpi' => [
                'total_members'  => $totalMembers,
                'active_members' => $activeMembers,
                'new_this_month' => $newThisMonth,
                'pending_reports'=> $pendingReports->count(),
            ],

            // Section 1
            'pending_reports'     => $pendingReports->values(),
            'pending_approvals_count' => 0, // kept for backward compat

            // Section 2: meetings table
            'meetings'            => $meetings->values(),

            // Section 3: attendance charts
            'dept_att_series'     => $deptAttLines->values(),
            'church_att_line'     => $churchLine,

            // Section 4: CGDG
            'cgdg'                => $cgdgData,

            // Section 5: birthdays
            'birthdays'           => $birthdayMembers->values(),

            // Section 6: visitations
            'visitations'         => $visitations->values(),
            'visitation_stats'    => $visitationStats,

            // Section 7: new members
            'new_members_30'      => $newMembers30->values(),
            'new_members_90'      => $newMembers90->values(),

            // Section 8: special dates
            'special_dates'       => $specialDates,
        ]);
    }
}
