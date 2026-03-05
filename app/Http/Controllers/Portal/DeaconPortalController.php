<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\FinanceFund;
use App\Models\FinanceTransaction;
use App\Models\DepartmentReport;
use Carbon\Carbon;
use Inertia\Inertia;

class DeaconPortalController extends Controller
{
    /**
     * Dashboard chính của Ban Chấp Sự.
     * Hiển thị context switcher (Thư Ký | Thủ Quỹ) + feature grid theo role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $activeRole = session('active_deacon_role', 'secretary');

        // Thống kê chung
        $totalMembers = Member::count();
        $month = now()->month;
        $year  = now()->year;
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd   = $monthStart->copy()->endOfMonth();

        $department = [
            'id' => $activeRole,
            'name' => $activeRole === 'secretary' ? 'Thư Ký Hội Thánh' : 'Thủ Quỹ Hội Thánh',
        ];

        $availableDepartments = [
            ['id' => 'secretary', 'name' => 'Thư Ký Hội Thánh'],
            ['id' => 'treasurer', 'name' => 'Thủ Quỹ Hội Thánh'],
        ];

        $data = [
            'activeRole'           => $activeRole,
            'department'           => $department,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin'        => $user->hasRole(['Pastor', 'Super_Admin']),
            'totalMembers'         => $totalMembers,
            'currentMonth'         => now()->format('m/Y'),
        ];

        // ── THƯ KÝ data ──────────────────────────────────────────
        if ($activeRole === 'secretary') {
            // Số buổi nhóm hội thánh tháng này chưa điểm danh
            $pendingAttendance = Meeting::where('type', 'church')
                ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->where('attendance_marked', false)
                ->count();

            // Buổi nhóm gần nhất
            $lastMeeting = Meeting::where('type', 'church')
                ->where('date', '<=', now()->toDateString())
                ->orderBy('date', 'desc')
                ->first();

            // Báo cáo đang chờ duyệt từ tất cả ban
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
                'name' => $lastMeeting->title ?? 'Buổi Nhóm HT',
            ] : null;
            $data['pendingReports']    = $pendingReports->values();
        }

        // ── THỦ QUỸ data ─────────────────────────────────────────
        if ($activeRole === 'treasurer') {
            // Quỹ hội thánh
            $funds = FinanceFund::where('owner_type', 'church')
                ->get(['id', 'name', 'balance'])
                ->map(fn($f) => [
                    'id'      => $f->id,
                    'name'    => $f->name,
                    'balance' => $f->balance,
                ]);

            // Tổng thu/chi tháng này
            $fundIds = FinanceFund::where('owner_type', 'church')->pluck('id');
            $totalIncome  = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->where('status', 'approved')->where('type', 'income')->sum('amount');
            $totalExpense = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->where('status', 'approved')->where('type', 'expense')->sum('amount');

            $pendingTx = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->where('status', 'pending')->count();

            $data['funds']        = $funds->values();
            $data['totalIncome']  = $totalIncome;
            $data['totalExpense'] = $totalExpense;
            $data['pendingTx']    = $pendingTx;
        }

        return Inertia::render('Deacon/Index', $data);
    }

    /**
     * Chuyển vai trò: secretary | treasurer
     */
    public function switchRole(Request $request)
    {
        $request->validate(['role' => 'required|in:secretary,treasurer']);
        session(['active_deacon_role' => $request->role]);
        return redirect()->route('deacon.index');
    }

    /**
     * Điểm danh buổi nhóm hội thánh
     */
    public function attendance(Request $request)
    {
        $meetings = Meeting::where('type', 'church')
            ->orderBy('date', 'desc')
            ->get(['id', 'title', 'date', 'time', 'location', 'attendance_marked']);

        $department = [
            'id' => 'secretary',
            'name' => 'Thư Ký Hội Thánh',
        ];

        return Inertia::render('Deacon/Attendance', [
            'meetings'   => $meetings,
            'department' => $department,
            'portalType' => 'deacon',
            'isGlobalAdmin' => $request->user()->hasRole(['Pastor', 'Super_Admin']),
            'availableDepartments' => [
                ['id' => 'secretary', 'name' => 'Thư Ký Hội Thánh'],
                ['id' => 'treasurer', 'name' => 'Thủ Quỹ Hội Thánh'],
            ]
        ]);
    }
}
