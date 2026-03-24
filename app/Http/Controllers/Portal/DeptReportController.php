<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\DepartmentReport;
use App\Models\Meeting;
use App\Models\MeetingAttendanceSummary;
use App\Models\MeetingFinance;
use App\Models\DepartmentFund;
use App\Models\Visitation;
use Carbon\Carbon;

class DeptReportController extends Controller
{
    // ─── Map a single meeting to a row array ──────────────────────
    private function mapMeetingRow(Meeting $m, int $deptId): array
    {
        $summary    = $m->attendanceSummaries->first();
        $attendance = $summary?->manual_count ?? 0;
        $income     = $m->finances->where('type', 'thu')->sum('amount');
        $expense    = $m->finances->where('type', 'chi')->sum('amount');
        $dt         = Carbon::parse($m->date);
        return [
            'id'          => $m->id,
            'date'        => $dt->format('d/m/Y'),
            'day'         => $dt->locale('vi')->isoFormat('dddd'),
            'week_no'     => (int) ceil($dt->day / 7),   // week within month (1-5)
            'type'        => $m->type,
            'topic'       => $m->topic ?? '',
            'memory_verse'=> $m->memory_verse ?? '',
            'scripture'   => $m->scripture ?? '',
            'speaker'     => $m->speaker?->name ?? $m->preacher ?? '',
            'attendance'  => $attendance,
            'income'      => $income,
            'expense'     => $expense,
            'balance'     => $income - $expense,
            'note'        => $summary?->notes ?? '',
        ];
    }

    // ─── Fetch meetings for a type/dept/period ────────────────────
    private function getMeetings(string $type, int $deptId, string $from, string $to): \Illuminate\Support\Collection
    {
        $q = Meeting::where('type', $type)
            ->whereBetween('date', [$from, $to])
            ->orderBy('date')
            ->with([
                'attendanceSummaries' => fn($q) => $q->where('department_id', $deptId),
                'finances',
                'speaker',
            ]);

        if ($type === 'church') {
            // This dept's attendance at church meetings (dept_id = null for general or = deptId if dept-specific church)
            $q->where(fn($q2) => $q2->where('department_id', $deptId)->orWhereNull('department_id'));
        } else {
            $q->where('department_id', $deptId);
        }
        return $q->get();
    }

    // ─── Build weekly summary (5 weeks) from meeting rows ─────────
    private function weeklyFromRows(array $rows): array
    {
        $byWeek = [];
        for ($w = 1; $w <= 5; $w++) {
            $wRows = array_filter($rows, fn($r) => $r['week_no'] === $w);
            $byWeek[] = [
                'week'       => "Tuần $w",
                'attendance' => array_sum(array_column($wRows, 'attendance')),
                'income'     => array_sum(array_column($wRows, 'income')),
                'expense'    => array_sum(array_column($wRows, 'expense')),
                'sessions'   => count($wRows),
            ];
        }
        return $byWeek;
    }

    // ============================================================
    // INDEX
    // ============================================================
    public function index(Request $request)
    {
        $deptId = session('active_portal_dept_id');
        if (!$deptId) return redirect()->route('portal.index');
        $department = Department::findOrFail($deptId);

        $this->authorizeFeature('reports');


        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year',  now()->year);

        $start     = Carbon::create($year, $month, 1)->startOfMonth();
        $end       = $start->copy()->endOfMonth();
        $prevStart = $start->copy()->subMonth()->startOfMonth();
        $prevEnd   = $prevStart->copy()->endOfMonth();
        $nextStart = $start->copy()->addMonth()->startOfMonth();
        $nextEnd   = $nextStart->copy()->endOfMonth();

        // ── Current month meetings ────────────────────────────────
        $churchMeetings = $this->getMeetings('church',     $deptId, $start->toDateString(), $end->toDateString());
        $deptMeetings   = $this->getMeetings('department', $deptId, $start->toDateString(), $end->toDateString());

        $churchRows = $churchMeetings->map(fn($m) => $this->mapMeetingRow($m, $deptId))->values()->toArray();
        $deptRows   = $deptMeetings->map(fn($m)   => $this->mapMeetingRow($m, $deptId))->values()->toArray();

        // ── Weekly breakdowns ─────────────────────────────────────
        $churchWeekly = $this->weeklyFromRows($churchRows);
        $deptWeekly   = $this->weeklyFromRows($deptRows);

        // ── Attendance averages ───────────────────────────────────
        $avgChurch = count($churchRows) > 0 ? round(array_sum(array_column($churchRows, 'attendance')) / count($churchRows), 1) : 0;
        $avgDept   = count($deptRows)   > 0 ? round(array_sum(array_column($deptRows,   'attendance')) / count($deptRows),   1) : 0;

        $prevChurch     = $this->getMeetings('church',     $deptId, $prevStart->toDateString(), $prevEnd->toDateString());
        $prevDept       = $this->getMeetings('department', $deptId, $prevStart->toDateString(), $prevEnd->toDateString());
        $prevChurchRows = $prevChurch->map(fn($m) => $this->mapMeetingRow($m, $deptId))->toArray();
        $prevDeptRows   = $prevDept->map(fn($m)   => $this->mapMeetingRow($m, $deptId))->toArray();
        $prevAvgChurch  = count($prevChurchRows) > 0 ? round(array_sum(array_column($prevChurchRows, 'attendance')) / count($prevChurchRows), 1) : 0;
        $prevAvgDept    = count($prevDeptRows)   > 0 ? round(array_sum(array_column($prevDeptRows,   'attendance')) / count($prevDeptRows),   1) : 0;
        
        $prevChurchWeekly = $this->weeklyFromRows($prevChurchRows);
        $prevDeptWeekly   = $this->weeklyFromRows($prevDeptRows);

        $calcChange = fn($c, $p) => $p > 0 ? round((($c - $p) / $p) * 100, 1) : ($c > 0 ? 100.0 : 0.0);

        // ── Combined bar chart: weekly attendance (both types) ────
        $WEEKS = ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4', 'Tuần 5'];
        $combinedWeekly = array_map(fn($i, $w) => [
            'week'   => $w,
            'church' => $churchWeekly[$i]['attendance'],
            'dept'   => $deptWeekly[$i]['attendance'],
        ], array_keys($WEEKS), $WEEKS);

        // ── Finance aggregates ────────────────────────────────────
        // Finance: only dept meetings have offerings (tiền dâng)
        $deptMeetingIds = $deptMeetings->pluck('id');
        $monthIncome  = MeetingFinance::whereIn('meeting_id', $deptMeetingIds)->where('type', 'thu')->sum('amount');
        $monthExpense = MeetingFinance::whereIn('meeting_id', $deptMeetingIds)->where('type', 'chi')->sum('amount');

        // Opening balance from all previous dept meetings
        $allPrevDeptIds = Meeting::where('type', 'department')->where('department_id', $deptId)
            ->where('date', '<', $start->toDateString())->pluck('id');
        $prevIn     = MeetingFinance::whereIn('meeting_id', $allPrevDeptIds)->where('type', 'thu')->sum('amount');
        $prevOut    = MeetingFinance::whereIn('meeting_id', $allPrevDeptIds)->where('type', 'chi')->sum('amount');
        $openingBal = $prevIn - $prevOut;

        // ── Dept-only weekly finance table ────────────────────────
        // Only dept meetings have tiền dâng
        $weeklyFinance  = $this->weeklyFromRows($deptRows);

        // Running balance per week
        $running = $openingBal;
        $weeklyFinance = array_map(function($w) use (&$running) {
            $running += $w['income'] - $w['expense'];
            return array_merge($w, ['running_balance' => $running]);
        }, $weeklyFinance);

        // ── 3-month combined finance chart (dept only, 1 chart 3 lines) ─
        $threeMonthChart = [];
        for ($i = 2; $i >= 0; $i--) {
            $mStart = $start->copy()->subMonths($i)->startOfMonth();
            $mEnd   = $mStart->copy()->endOfMonth();
            $mDept  = $this->getMeetings('department', $deptId, $mStart->toDateString(), $mEnd->toDateString());
            $mRows  = $mDept->map(fn($m) => $this->mapMeetingRow($m, $deptId))->toArray();
            $mWeekly = $this->weeklyFromRows($mRows);
            $threeMonthChart[] = [
                'label'  => $mStart->locale('vi')->isoFormat('MMMM/YY'),
                'income' => array_column($mWeekly, 'income'),
                'expense'=> array_column($mWeekly, 'expense'),
            ];
        }

        // ── Visitation ────────────────────────────────────────────
        $visitAll = Visitation::where('department_id', $deptId)
            ->whereBetween('visit_date', [$start->toDateString(), $end->toDateString()])
            ->with(['member', 'visitors'])->orderBy('visit_date')->get();

        $visitRows      = $visitAll->map(fn($v) => [
            'id'          => $v->id,
            'visit_date'  => $v->visit_date->format('d/m/Y'),
            'member_name' => $v->member?->full_name ?? '—',
            'reason'      => $v->reason ?? '',
            'content'     => $v->content ?? '',
            'status'      => $v->status ?? 'planned',
            'visitors'    => $v->visitors->map(fn($vis) => $vis->full_name)->join(', '),
        ]);
        $visitPlanned   = $visitAll->count();
        $visitCompleted = $visitAll->where('status', 'completed')->count();
        $visitPct       = $visitPlanned > 0 ? round($visitCompleted / $visitPlanned * 100) : 0;

        // ── Next month schedule ───────────────────────────────────
        $nextMonthMeetings = Meeting::where('department_id', $deptId)->where('type', 'department')
            ->whereBetween('date', [$nextStart->toDateString(), $nextEnd->toDateString()])
            ->orderBy('date')->with('speaker')->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'date'         => Carbon::parse($m->date)->format('d/m/Y'),
                'day'          => Carbon::parse($m->date)->locale('vi')->isoFormat('dddd'),
                'type'         => $m->type,
                'topic'        => $m->topic ?? '',
                'scripture'    => $m->scripture ?? '',
                'memory_verse' => $m->memory_verse ?? '',
                'preacher'     => $m->speaker?->name ?? $m->preacher ?? '',
                'is_dept'      => $m->type === 'department',
            ]);

        // ── Fund balances ─────────────────────────────────────────
        $fundBalances = DepartmentFund::where('department_id', $deptId)->get()
            ->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'balance' => $f->balance]);

        // ── Report narrative ──────────────────────────────────────
        $report = DepartmentReport::where('department_id', $deptId)
            ->where('report_month', $month)->where('report_year', $year)->first();

        return Inertia::render('Portal/Reports/Index', [
            'department'           => $department,
            'availableDepartments' => $this->getAvailableDepartments($request->user()),
            'isGlobalAdmin'        => $request->user()->isSuperAdmin(),
            'canCreate' => app(\App\Services\PortalService::class)->canManage($request->user(), (int)$deptId, 'reports'),
            'canApprove' => $request->user()->isSuperAdmin(),
            'filters'              => ['month' => $month, 'year' => $year],

            // Meeting tables
            'church_meetings'      => $churchRows,
            'dept_meetings'        => $deptRows,

            // Charts: weekly attendance data
            'church_weekly'        => $churchWeekly,   // [{week, attendance, sessions}, ...]
            'dept_weekly'          => $deptWeekly,
            'prev_church_weekly'   => $prevChurchWeekly,
            'prev_dept_weekly'     => $prevDeptWeekly,
            'combined_weekly'      => $combinedWeekly, // [{week, church, dept}, ...]

            // Finance
            'weekly_finance'       => $weeklyFinance,  // [{week, income, expense, sessions, running_balance}, ...]
            'three_month_chart'    => $threeMonthChart, // [{label, income:[5], expense:[5]}, ...]

            'visitations'          => $visitRows->values(),
            'next_month_meetings'  => $nextMonthMeetings->values(),
            'next_month_label'     => $nextStart->locale('vi')->isoFormat('MMMM YYYY'),

            'summary' => [
                'month_income'    => $monthIncome,
                'month_expense'   => $monthExpense,
                'opening_balance' => $openingBal,
                'closing_balance' => $openingBal + $monthIncome - $monthExpense,
                'avg_church'      => $avgChurch,
                'avg_dept'        => $avgDept,
                'prev_avg_church' => $prevAvgChurch,
                'prev_avg_dept'   => $prevAvgDept,
                'church_change'   => $calcChange($avgChurch, $prevAvgChurch),
                'dept_change'     => $calcChange($avgDept, $prevAvgDept),
                'church_count'    => count($churchRows),
                'dept_count'      => count($deptRows),
                'visit_planned'   => $visitPlanned,
                'visit_completed' => $visitCompleted,
                'visit_pct'       => $visitPct,
            ],
            'fund_balances' => $fundBalances,
            'report'        => $report,
        ]);
    }

    // ============================================================
    public function saveReport(Request $request)
    {
        $this->authorizeManage('reports');

        $deptId = session('active_portal_dept_id');
        if (!$deptId) abort(403);
        $v = $request->validate([
            'report_month'     => 'required|integer|min:1|max:12',
            'report_year'      => 'required|integer|min:2020|max:2099',
            'reporter_name'    => 'nullable|string|max:255',
            'evaluation'       => 'nullable|string',
            'request'          => 'nullable|string',
            'proposals'        => 'nullable|string',
            'activities_notes' => 'nullable|string',
        ]);
        $report = DepartmentReport::updateOrCreate(
            ['department_id' => $deptId, 'report_month' => $v['report_month'], 'report_year' => $v['report_year']],
            array_merge($v, ['status' => 'submitted'])
        );
        
        $department = Department::find($deptId);
        $notifiers = \App\Models\User::role(['Super_Admin', 'Pastor'])->get();
        \Illuminate\Support\Facades\Notification::send($notifiers, new \App\Notifications\ReportSubmittedNotification($report, $department->name ?? ''));

        return back()->with('message', 'Báo cáo đã được lưu.');
    }

    public function approveReport(Request $request, DepartmentReport $report)
    {
        $this->authorizeManage('reports');

        $report->update(['status' => 'approved']);
        return back()->with('message', 'Báo cáo đã được duyệt.');
    }

    private function getAvailableDepartments($user): \Illuminate\Support\Collection
    {
        return app(\App\Services\PortalService::class)->getAvailableDepartments($user, 'activities');
    }
}
