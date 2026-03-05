<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Department;
use App\Models\Meeting;
use App\Models\MeetingAttendanceSummary;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DeaconPortalController extends Controller
{
    /**
     * Dashboard chính của Ban Chấp Sự.
     * Hiển thị 2 card: Thư Ký / Thủ Quỹ và thông tin tổng quan.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $activeRole = session('active_deacon_role', 'secretary');

        // Nếu đang chọn Thủ Quỹ → redirect sang Finance Portal
        if ($activeRole === 'treasurer') {
            return redirect()->route('finance.index');
        }

        // Thống kê nhanh cho dashboard
        $totalMembers  = Member::count();
        $officialCount = Member::where('status', 'Chính thức')->count();
        $month = now()->month;
        $year  = now()->year;
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        // Báo cáo đang chờ duyệt từ tất cả ban
        $pendingReports = \App\Models\DepartmentReport::where('status', 'submitted')
            ->with('department')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'dept_name'    => $r->department->name ?? '—',
                'month'        => $r->report_month,
                'year'         => $r->report_year,
                'submitted_at' => $r->updated_at->format('d/m/Y'),
            ]);

        // Điểm danh tổng quát tháng này theo ban
        $depts = Department::where('block', 'activities')->select('id', 'name')->get();
        $attendanceSummary = $depts->map(function ($dept) use ($monthStart, $monthEnd) {
            $total = Meeting::where('type', 'department')
                ->where('department_id', $dept->id)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->with('attendanceSummaries')
                ->get()
                ->sum(fn($m) => $m->attendanceSummaries->sum('manual_count'));
            $sessionCount = Meeting::where('type', 'department')
                ->where('department_id', $dept->id)
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->count();
            return [
                'dept_name'     => $dept->name,
                'session_count' => $sessionCount,
                'total_att'     => $total,
            ];
        });

        return Inertia::render('Deacon/Index', [
            'activeRole'        => $activeRole,
            'stats'             => [
                'total_members'  => $totalMembers,
                'official_count' => $officialCount,
                'pending_reports'=> $pendingReports->count(),
            ],
            'pendingReports'    => $pendingReports->values(),
            'attendanceSummary' => $attendanceSummary->values(),
            'currentMonth'      => now()->format('m/Y'),
        ]);
    }

    /**
     * Chuyển vai trò: secretary | treasurer
     */
    public function switchRole(Request $request)
    {
        $request->validate(['role' => 'required|in:secretary,treasurer']);
        session(['active_deacon_role' => $request->role]);

        if ($request->role === 'treasurer') {
            return redirect()->route('finance.index');
        }

        return redirect()->route('deacon.index');
    }
}
