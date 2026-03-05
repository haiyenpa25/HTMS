<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\DeaconAttendanceRecord;
use App\Models\DeaconMonthlyReport;
use App\Models\DeaconReportIncident;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\FinanceFund;
use App\Models\FinanceTransaction;
use App\Models\DepartmentReport;
use App\Models\MeetingAttendanceSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeaconPortalController extends Controller
{
    /* ─────────────────────────────────────────────────────────────
     |  HELPERS
     */
    private function deaconMeta(Request $request): array
    {
        $activeRole = session('active_deacon_role', 'secretary');
        return [
            'activeRole'           => $activeRole,
            'department'           => [
                'id'   => $activeRole,
                'name' => $activeRole === 'secretary' ? 'Thư Ký Hội Thánh' : 'Thủ Quỹ Hội Thánh',
            ],
            'availableDepartments' => [
                ['id' => 'secretary', 'name' => 'Thư Ký Hội Thánh'],
                ['id' => 'treasurer', 'name' => 'Thủ Quỹ Hội Thánh'],
            ],
            'isGlobalAdmin' => $request->user()->hasRole(['Pastor', 'Super_Admin']),
        ];
    }

    /* ─────────────────────────────────────────────────────────────
     |  DASHBOARD  (index)
     */
    public function index(Request $request)
    {
        $user      = $request->user();
        $activeRole = session('active_deacon_role', 'secretary');

        $totalMembers = Member::count();
        $month        = now()->month;
        $year         = now()->year;
        $monthStart   = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd     = $monthStart->copy()->endOfMonth();

        $data = array_merge($this->deaconMeta($request), [
            'totalMembers' => $totalMembers,
            'currentMonth' => now()->format('m/Y'),
        ]);

        if ($activeRole === 'secretary') {
            $pendingAttendance = Meeting::where('type', 'church')
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->where('attendance_marked', false)
                ->count();

            $lastMeeting = Meeting::where('type', 'church')
                ->where('date', '<=', now()->toDateString())
                ->orderBy('date', 'desc')
                ->first();

            $pendingReports = DepartmentReport::where('status', 'submitted')
                ->with('department:id,name')
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(fn($r) => [
                    'id'        => $r->id,
                    'dept_name' => $r->department->name ?? '—',
                    'month'     => $r->report_month,
                    'year'      => $r->report_year,
                ]);

            $data['pendingAttendance'] = $pendingAttendance;
            $data['lastMeeting']       = $lastMeeting ? [
                'id'   => $lastMeeting->id,
                'date' => $lastMeeting->date,
                'name' => $lastMeeting->topic ?? 'Buổi Nhóm HT',
            ] : null;
            $data['pendingReports'] = $pendingReports->values();
        }

        if ($activeRole === 'treasurer') {
            $funds = FinanceFund::where('owner_type', 'church')
                ->get(['id', 'name', 'balance'])
                ->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'balance' => $f->balance]);

            $fundIds      = FinanceFund::where('owner_type', 'church')->pluck('id');
            $totalIncome  = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->where('status', 'approved')->where('type', 'income')->sum('amount');
            $totalExpense = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->where('status', 'approved')->where('type', 'expense')->sum('amount');
            $pendingTx    = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->where('status', 'pending')->count();

            $data['funds']        = $funds->values();
            $data['totalIncome']  = $totalIncome;
            $data['totalExpense'] = $totalExpense;
            $data['pendingTx']    = $pendingTx;
        }

        return Inertia::render('Deacon/Index', $data);
    }

    /* ─────────────────────────────────────────────────────────────
     |  SWITCH ROLE
     */
    public function switchRole(Request $request)
    {
        $request->validate(['role' => 'required|in:secretary,treasurer']);
        session(['active_deacon_role' => $request->role]);
        return redirect()->route('deacon.index');
    }

    /* ─────────────────────────────────────────────────────────────
     |  ATTENDANCE — List
     */
    public function attendance(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $meetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with(['speaker', 'attendanceRecord'])
            ->orderBy('date')
            ->get()
            ->map(fn($m) => [
                'id'                => $m->id,
                'date'              => $m->date,
                'time'              => $m->time,
                'topic'             => $m->topic,
                'memory_verse'      => $m->memory_verse,
                'scripture'         => $m->scripture,
                'preacher'          => $m->preacher,
                'speaker_name'      => $m->speaker?->full_name,
                'attendance_marked' => $m->attendance_marked,
                'record'            => $m->attendanceRecord ? [
                    'id'             => $m->attendanceRecord->id,
                    'total_present'  => $m->attendanceRecord->total_present,
                    'total_online'   => $m->attendanceRecord->total_online,
                    'total_children' => $m->attendanceRecord->total_children,
                    'total_visitors' => $m->attendanceRecord->total_visitors,
                    'notes'          => $m->attendanceRecord->notes,
                ] : null,
            ]);

        // Build year options (last 3 years)
        $currentYear  = now()->year;
        $yearOptions  = range($currentYear - 2, $currentYear + 1);
        $monthOptions = range(1, 12);

        return Inertia::render('Deacon/Attendance', array_merge($this->deaconMeta($request), [
            'meetings'     => $meetings,
            'filterMonth'  => $month,
            'filterYear'   => $year,
            'yearOptions'  => $yearOptions,
            'monthOptions' => $monthOptions,
        ]));
    }

    /* ─────────────────────────────────────────────────────────────
     |  ATTENDANCE — Show single meeting
     */
    public function attendanceShow(Request $request, Meeting $meeting)
    {
        $record = DeaconAttendanceRecord::where('meeting_id', $meeting->id)->first();

        // Prior month average for comparison
        $meetingDate = Carbon::parse($meeting->date);
        $prevStart   = $meetingDate->copy()->subMonth()->startOfMonth();
        $prevEnd     = $prevStart->copy()->endOfMonth();

        $prevAvg = DeaconAttendanceRecord::whereHas('meeting', function ($q) use ($prevStart, $prevEnd) {
            $q->where('type', 'church')
              ->whereBetween('date', [$prevStart->toDateString(), $prevEnd->toDateString()]);
        })->avg('total_present');

        return Inertia::render('Deacon/AttendanceShow', array_merge($this->deaconMeta($request), [
            'meeting' => [
                'id'           => $meeting->id,
                'date'         => $meeting->date,
                'time'         => $meeting->time,
                'topic'        => $meeting->topic,
                'memory_verse' => $meeting->memory_verse,
                'scripture'    => $meeting->scripture,
                'preacher'     => $meeting->preacher,
                'speaker_name' => $meeting->speaker?->full_name,
            ],
            'record'  => $record ? [
                'id'             => $record->id,
                'total_present'  => $record->total_present,
                'total_online'   => $record->total_online,
                'total_children' => $record->total_children,
                'total_visitors' => $record->total_visitors,
                'notes'          => $record->notes,
            ] : null,
            'prevMonthAvg' => round($prevAvg ?? 0),
        ]));
    }

    /* ─────────────────────────────────────────────────────────────
     |  ATTENDANCE — Store / Update
     */
    public function attendanceStore(Request $request, Meeting $meeting)
    {
        $data = $request->validate([
            'total_present'  => 'required|integer|min:0',
            'total_online'   => 'nullable|integer|min:0',
            'total_children' => 'nullable|integer|min:0',
            'total_visitors' => 'nullable|integer|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $data['meeting_id']    = $meeting->id;
        $data['recorded_by']   = $request->user()->id;
        $data['total_online']   = $data['total_online'] ?? 0;
        $data['total_children'] = $data['total_children'] ?? 0;
        $data['total_visitors'] = $data['total_visitors'] ?? 0;

        DeaconAttendanceRecord::updateOrCreate(
            ['meeting_id' => $meeting->id],
            $data
        );

        // Mark meeting as attendance_marked
        $meeting->update(['attendance_marked' => true]);

        return redirect()->route('deacon.attendance', [
            'month' => Carbon::parse($meeting->date)->month,
            'year'  => Carbon::parse($meeting->date)->year,
        ])->with('success', 'Đã lưu số liệu điểm danh!');
    }

    /* ─────────────────────────────────────────────────────────────
     |  REPORT — index (tháng hiện tại / chọn tháng)
     */
    public function report(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        // Load/create draft report
        $report = DeaconMonthlyReport::with('incidents')
            ->firstOrCreate(
                ['report_month' => $month, 'report_year' => $year],
                ['status' => 'draft', 'submitted_by' => $request->user()->id]
            );

        // Meetings of this month
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $meetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with(['speaker', 'attendanceRecord'])
            ->orderBy('date')
            ->get()
            ->map(fn($m) => [
                'id'           => $m->id,
                'date'         => $m->date,
                'topic'        => $m->topic,
                'memory_verse' => $m->memory_verse,
                'scripture'    => $m->scripture,
                'preacher'     => $m->preacher,
                'speaker_name' => $m->speaker?->full_name,
                'total_present' => $m->attendanceRecord?->total_present ?? 0,
                'total_online'  => $m->attendanceRecord?->total_online ?? 0,
            ]);

        // Attendance chart: last 6 months
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt    = Carbon::create($year, $month, 1)->subMonths($i);
            $start = $dt->copy()->startOfMonth()->toDateString();
            $end   = $dt->copy()->endOfMonth()->toDateString();

            $avg = DeaconAttendanceRecord::whereHas('meeting', function ($q) use ($start, $end) {
                $q->where('type', 'church')->whereBetween('date', [$start, $end]);
            })->avg('total_present');

            $chartData[] = [
                'label'   => $dt->format('m/Y'),
                'present' => round($avg ?? 0),
            ];
        }

        // Pie chart: attendance by department (from meeting_attendance_summaries)
        $deptAttendance = MeetingAttendanceSummary::whereHas('meeting', function ($q) use ($monthStart, $monthEnd) {
            $q->where('type', 'church')
              ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
        })->with('department:id,name')
          ->get()
          ->groupBy('department_id')
          ->map(fn($rows, $deptId) => [
              'dept_name' => $rows->first()->department?->name ?? 'Không xác định',
              'total'     => $rows->sum('manual_count'),
          ])
          ->values();

        $currentYear = now()->year;
        $yearOptions = range($currentYear - 2, $currentYear + 1);

        return Inertia::render('Deacon/Report', array_merge($this->deaconMeta($request), [
            'report'         => [
                'id'                 => $report->id,
                'report_month'       => $report->report_month,
                'report_year'        => $report->report_year,
                'yt_subscribers'     => $report->yt_subscribers,
                'yt_new_subscribers' => $report->yt_new_subscribers,
                'yt_views'           => $report->yt_views,
                'yt_watch_hours'     => $report->yt_watch_hours,
                'announcements'      => $report->announcements,
                'summary_notes'      => $report->summary_notes,
                'status'             => $report->status,
                'incidents'          => $report->incidents->map(fn($i) => [
                    'id'                   => $i->id,
                    'week_label'           => $i->week_label,
                    'incident_description' => $i->incident_description,
                    'resolution'           => $i->resolution,
                    'direction'            => $i->direction,
                    'status'               => $i->status,
                ])->values(),
            ],
            'meetings'       => $meetings,
            'chartData'      => $chartData,
            'deptAttendance' => $deptAttendance,
            'filterMonth'    => $month,
            'filterYear'     => $year,
            'yearOptions'    => $yearOptions,
        ]));
    }

    /* ─────────────────────────────────────────────────────────────
     |  REPORT — Save (YouTube + announcements)
     */
    public function reportSave(Request $request)
    {
        $data = $request->validate([
            'report_id'          => 'required|exists:deacon_monthly_reports,id',
            'yt_subscribers'     => 'nullable|integer|min:0',
            'yt_new_subscribers' => 'nullable|integer|min:0',
            'yt_views'           => 'nullable|integer|min:0',
            'yt_watch_hours'     => 'nullable|integer|min:0',
            'announcements'      => 'nullable|string|max:3000',
            'summary_notes'      => 'nullable|string|max:3000',
        ]);

        $report = DeaconMonthlyReport::findOrFail($data['report_id']);
        $report->update([
            'yt_subscribers'     => $data['yt_subscribers'] ?? 0,
            'yt_new_subscribers' => $data['yt_new_subscribers'] ?? 0,
            'yt_views'           => $data['yt_views'] ?? 0,
            'yt_watch_hours'     => $data['yt_watch_hours'] ?? 0,
            'announcements'      => $data['announcements'],
            'summary_notes'      => $data['summary_notes'],
        ]);

        return back()->with('success', 'Đã lưu báo cáo!');
    }

    /* ─────────────────────────────────────────────────────────────
     |  REPORT — Incident CRUD
     */
    public function reportIncidentStore(Request $request)
    {
        $data = $request->validate([
            'report_id'            => 'required|exists:deacon_monthly_reports,id',
            'week_label'           => 'required|string|max:50',
            'incident_description' => 'nullable|string|max:2000',
            'resolution'           => 'nullable|string|max:2000',
            'direction'            => 'nullable|string|max:2000',
            'status'               => 'required|in:pending,in_progress,resolved',
        ]);

        $incident = DeaconReportIncident::create([
            'deacon_report_id'     => $data['report_id'],
            'week_label'           => $data['week_label'],
            'incident_description' => $data['incident_description'],
            'resolution'           => $data['resolution'],
            'direction'            => $data['direction'],
            'status'               => $data['status'],
        ]);

        return back()->with('success', 'Đã thêm sự cố!');
    }

    public function reportIncidentUpdate(Request $request, DeaconReportIncident $incident)
    {
        $data = $request->validate([
            'week_label'           => 'required|string|max:50',
            'incident_description' => 'nullable|string|max:2000',
            'resolution'           => 'nullable|string|max:2000',
            'direction'            => 'nullable|string|max:2000',
            'status'               => 'required|in:pending,in_progress,resolved',
        ]);

        $incident->update($data);
        return back()->with('success', 'Đã cập nhật sự cố!');
    }

    public function reportIncidentDestroy(DeaconReportIncident $incident)
    {
        $incident->delete();
        return back()->with('success', 'Đã xoá sự cố!');
    }
}
