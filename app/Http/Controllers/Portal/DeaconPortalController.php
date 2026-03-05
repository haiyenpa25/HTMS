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
        $user       = $request->user();
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
     |  ATTENDANCE — List (PAGINATED — same as AttendanceController)
     */
    public function attendance(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Paginated — same pattern as portal AttendanceController
        $meetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with(['speaker', 'attendanceRecord'])
            ->orderBy('date')
            ->paginate(15)
            ->withQueryString()
            ->through(fn($m) => [
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
                    'total_visitors' => $m->attendanceRecord->total_visitors,
                    'notes'          => $m->attendanceRecord->notes,
                ] : null,
            ]);

        return Inertia::render('Deacon/Attendance', array_merge($this->deaconMeta($request), [
            'meetings' => $meetings,
            'filters'  => ['month' => $month, 'year' => $year],
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
            'record'      => $record ? [
                'id'             => $record->id,
                'total_present'  => $record->total_present,
                'total_online'   => $record->total_online,
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
            'total_visitors' => 'nullable|integer|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $data['meeting_id']    = $meeting->id;
        $data['recorded_by']   = $request->user()->id;
        $data['total_online']   = $data['total_online']   ?? 0;
        $data['total_visitors'] = $data['total_visitors'] ?? 0;

        DeaconAttendanceRecord::updateOrCreate(
            ['meeting_id' => $meeting->id],
            $data
        );

        $meeting->update(['attendance_marked' => true]);

        return redirect()->route('deacon.attendance', [
            'month' => Carbon::parse($meeting->date)->month,
            'year'  => Carbon::parse($meeting->date)->year,
        ])->with('success', 'Đã lưu số liệu điểm danh!');
    }

    /* ─────────────────────────────────────────────────────────────
     |  REPORT — index (matches new Report.vue data shape)
     */
    public function report(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year  = (int) $request->get('year',  now()->year);

        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Load or create draft report
        $report = DeaconMonthlyReport::firstOrCreate(
            ['report_month' => $month, 'report_year' => $year],
            ['status' => 'draft', 'submitted_by' => $request->user()->id]
        );

        // Incidents for this report
        $incidents = DeaconReportIncident::where('deacon_report_id', $report->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn($i) => [
                'id'          => $i->id,
                'week_label'  => $i->week_label,
                'description' => $i->incident_description,  // mapped for frontend
                'resolution'  => $i->resolution,
                'direction'   => $i->direction,
                'status'      => $i->status,
            ])->values();

        // Church meetings this month
        $church_meetings = Meeting::where('type', 'church')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->with(['speaker', 'attendanceRecord'])
            ->orderBy('date')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'date'       => Carbon::parse($m->date)->format('d/m'),
                'day'        => Carbon::parse($m->date)->isoFormat('dddd'),
                'topic'      => $m->topic,
                'scripture'  => $m->scripture,
                'memory_verse' => $m->memory_verse,
                'speaker'    => $m->speaker?->full_name ?? $m->preacher,
                'attendance' => $m->attendanceRecord?->total_present ?? 0,
                'online'     => $m->attendanceRecord?->total_online  ?? 0,
            ])->values();

        // Summary stats
        $currentAvg  = $church_meetings->avg('attendance') ?? 0;
        $currentAvg  = round($currentAvg);

        // Previous month avg for change %
        $prevMonthStart = $monthStart->copy()->subMonth()->startOfMonth();
        $prevMonthEnd   = $prevMonthStart->copy()->endOfMonth();
        $prevAvg = DeaconAttendanceRecord::whereHas('meeting', function ($q) use ($prevMonthStart, $prevMonthEnd) {
            $q->where('type', 'church')->whereBetween('date', [$prevMonthStart->toDateString(), $prevMonthEnd->toDateString()]);
        })->avg('total_present') ?? 0;
        $prevAvg = round($prevAvg);

        $churchChange = $prevAvg > 0 ? round((($currentAvg - $prevAvg) / $prevAvg) * 100) : 0;

        $summary = [
            'avg_church'    => $currentAvg,
            'prev_avg'      => $prevAvg,
            'church_change' => $churchChange,
        ];

        // 6-month trend for bar chart
        $monthly_trend = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt    = Carbon::create($year, $month, 1)->subMonths($i);
            $start = $dt->copy()->startOfMonth()->toDateString();
            $end   = $dt->copy()->endOfMonth()->toDateString();

            $avg = DeaconAttendanceRecord::whereHas('meeting', function ($q) use ($start, $end) {
                $q->where('type', 'church')->whereBetween('date', [$start, $end]);
            })->avg('total_present') ?? 0;

            $monthly_trend[] = [
                'label' => $dt->format('m/Y'),
                'avg'   => round($avg),
            ];
        }

        // YouTube trend (3 months) for line chart
        $yt_trend_raw = [];
        for ($i = 2; $i >= 0; $i--) {
            $dt  = Carbon::create($year, $month, 1)->subMonths($i);
            $ytR = DeaconMonthlyReport::where('report_month', $dt->month)
                ->where('report_year', $dt->year)->first();
            $yt_trend_raw[] = [
                'label'       => $dt->format('m/Y'),
                'subscribers' => $ytR?->yt_subscribers     ?? 0,
                'new_subs'    => $ytR?->yt_new_subscribers ?? 0,
                'views'       => $ytR?->yt_views           ?? 0,
            ];
        }

        $yt_trend_series = [
            ['name' => 'Đăng ký',     'data' => array_column($yt_trend_raw, 'subscribers')],
            ['name' => 'Đăng ký mới', 'data' => array_column($yt_trend_raw, 'new_subs')],
            ['name' => 'Lượt xem',    'data' => array_column($yt_trend_raw, 'views')],
        ];

        // YouTube stats for input fields
        $youtube_stats = [
            'subscribers_current' => $report->yt_subscribers     ?? 0,
            'subscribers_new'     => $report->yt_new_subscribers ?? 0,
            'views'               => $report->yt_views           ?? 0,
            'watch_hours'         => $report->yt_watch_hours     ?? 0,
        ];

        // Report data for frontend
        $reportData = [
            'id'            => $report->id,
            'report_month'  => $report->report_month,
            'report_year'   => $report->report_year,
            'status'        => $report->status,
            'reporter_name' => $report->reporter_name,
            'evaluation'    => $report->evaluation ?? $report->summary_notes,
            'proposals'     => $report->proposals,
            'notes'         => $report->notes ?? $report->announcements,
        ];

        return Inertia::render('Deacon/Report', array_merge($this->deaconMeta($request), [
            'filters'        => ['month' => $month, 'year' => $year],
            'report'         => $reportData,
            'incidents'      => $incidents,
            'church_meetings'=> $church_meetings,
            'summary'        => $summary,
            'monthly_trend'  => $monthly_trend,
            'yt_trend_series'=> $yt_trend_series,
            'youtube_stats'  => $youtube_stats,
        ]));
    }

    /* ─────────────────────────────────────────────────────────────
     |  REPORT — Save
     */
    public function reportSave(Request $request)
    {
        $data = $request->validate([
            'report_month'         => 'required|integer|min:1|max:12',
            'report_year'          => 'required|integer|min:2020|max:2099',
            // YouTube fields
            'subscribers_current'  => 'nullable|integer|min:0',
            'subscribers_new'      => 'nullable|integer|min:0',
            'views'                => 'nullable|integer|min:0',
            'watch_hours'          => 'nullable|integer|min:0',
            // Report fields
            'reporter_name'        => 'nullable|string|max:100',
            'evaluation'           => 'nullable|string|max:3000',
            'proposals'            => 'nullable|string|max:3000',
            'notes'                => 'nullable|string|max:3000',
        ]);

        $user   = $request->user();
        $report = DeaconMonthlyReport::firstOrCreate(
            ['report_month' => $data['report_month'], 'report_year' => $data['report_year']],
            ['status' => 'draft', 'submitted_by' => $user->id]
        );

        $report->update([
            'yt_subscribers'     => $data['subscribers_current'] ?? 0,
            'yt_new_subscribers' => $data['subscribers_new']     ?? 0,
            'yt_views'           => $data['views']               ?? 0,
            'yt_watch_hours'     => $data['watch_hours']         ?? 0,
            'reporter_name'      => $data['reporter_name'],
            'evaluation'         => $data['evaluation'],
            'summary_notes'      => $data['evaluation'],   // keep in sync
            'proposals'          => $data['proposals'],
            'notes'              => $data['notes'],
            'announcements'      => $data['notes'],        // keep in sync
        ]);

        return back()->with('success', 'Đã lưu báo cáo!');
    }

    /* ─────────────────────────────────────────────────────────────
     |  REPORT — Incident CRUD
     */
    public function reportIncidentStore(Request $request)
    {
        $data = $request->validate([
            'report_month' => 'required|integer|min:1|max:12',
            'report_year'  => 'required|integer|min:2020|max:2099',
            'week_label'   => 'required|string|max:100',
            'description'  => 'nullable|string|max:2000',
            'resolution'   => 'nullable|string|max:2000',
            'direction'    => 'nullable|string|max:2000',
            'status'       => 'required|in:pending,in_progress,resolved',
        ]);

        $user   = $request->user();
        $report = DeaconMonthlyReport::firstOrCreate(
            ['report_month' => $data['report_month'], 'report_year' => $data['report_year']],
            ['status' => 'draft', 'submitted_by' => $user->id]
        );

        DeaconReportIncident::create([
            'deacon_report_id'     => $report->id,
            'week_label'           => $data['week_label'],
            'incident_description' => $data['description'],
            'resolution'           => $data['resolution'],
            'direction'            => $data['direction'],
            'status'               => $data['status'],
        ]);

        // Return updated incidents list
        $incidents = DeaconReportIncident::where('deacon_report_id', $report->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn($i) => [
                'id'          => $i->id,
                'week_label'  => $i->week_label,
                'description' => $i->incident_description,
                'resolution'  => $i->resolution,
                'direction'   => $i->direction,
                'status'      => $i->status,
            ])->values();

        return back()->with(['success' => 'Đã thêm sự cố!', 'incidents' => $incidents]);
    }

    public function reportIncidentUpdate(Request $request, DeaconReportIncident $incident)
    {
        $data = $request->validate([
            'week_label'  => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'resolution'  => 'nullable|string|max:2000',
            'direction'   => 'nullable|string|max:2000',
            'status'      => 'required|in:pending,in_progress,resolved',
        ]);

        $incident->update([
            'week_label'           => $data['week_label'],
            'incident_description' => $data['description'],
            'resolution'           => $data['resolution'],
            'direction'            => $data['direction'],
            'status'               => $data['status'],
        ]);

        return back()->with('success', 'Đã cập nhật sự cố!');
    }

    public function reportIncidentDestroy(DeaconReportIncident $incident)
    {
        $incident->delete();
        return back()->with('success', 'Đã xóa sự cố!');
    }
}
