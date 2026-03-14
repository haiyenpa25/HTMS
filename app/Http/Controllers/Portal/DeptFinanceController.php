<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\DepartmentFund;
use App\Models\Meeting;
use App\Models\MeetingFinance;
use Carbon\Carbon;

class DeptFinanceController extends Controller
{
    /**
     * Shared helper: get and validate active department from session.
     */
    private function getActiveDepartment(): Department
    {
        $deptId = session('active_portal_dept_id');
        if (!$deptId) {
            abort(redirect()->route('portal.index'));
        }
        return Department::findOrFail($deptId);
    }

    /**
     * Check that user belongs to the department (or is global admin/pastor).
     */
    private function authorizeForDept(Department $department, Request $request): void
    {
        $user = $request->user();
        if ($user->isSuperAdmin()) {
            return; // Full access
        }

        // 1. MAC check
        $hasMac = \App\Models\UserDepartmentFeature::where('user_id', $user->id)
            ->where('department_id', $department->id)
            ->where('is_enabled', true)
            ->whereHas('feature', fn($q) => $q->where('slug', 'finance'))
            ->exists();
        if ($hasMac) return;

        // 2. Legacy Membership check
        $memberId = $user->member_id;
        if ($memberId) {
            $belongs = DB::table('org_memberships')
                ->where('model_type', Department::class)
                ->where('model_id', $department->id)
                ->where('member_id', $memberId)
                ->exists();

            if ($belongs) return;
        }

        abort(403, 'Bạn chưa được cấp quyền Tài chính cho ban ngành này.');
    }

    // ============================================================
    // INDEX — Main Finance Page
    // ============================================================
    public function index(Request $request)
    {
        $department = $this->getActiveDepartment();
        $this->authorizeForDept($department, $request);

        $month = (int) $request->input('month', date('m'));
        $year  = (int) $request->input('year', date('Y'));

        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd   = $currentStart->copy()->endOfMonth();
        $prevEnd      = $currentStart->copy()->subDay(); // last day of previous month

        // Meetings (type=department) in this month for this department
        $meetings = Meeting::where('type', 'department')
            ->where('department_id', $department->id)
            ->whereBetween('date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->orderBy('date', 'asc')
            ->with(['finances', 'attendanceSummaries' => fn($q) => $q->where('department_id', $department->id)])
            ->get()
            ->map(fn($m) => [
                'id'             => $m->id,
                'meeting_date'   => $m->date,
                'topic'          => $m->topic ?? '',
                'scripture'      => $m->scripture ?? '',
                'memory_verse'   => $m->memory_verse ?? '',
                'attendance'     => $m->attendanceSummaries->first()?->manual_count ?? 0,
                'session_income' => $m->finances->where('type', 'thu')->sum('amount'),
                'session_expense'=> $m->finances->where('type', 'chi')->sum('amount'),
                'session_balance'=> $m->finances->where('type', 'thu')->sum('amount')
                                  - $m->finances->where('type', 'chi')->sum('amount'),
                'finances'       => $m->finances->map(fn($f) => [
                    'id'          => $f->id,
                    'type'        => $f->type,   // 'thu' or 'chi'
                    'amount'      => $f->amount,
                    'category'    => $f->category ?? '',
                    'status'      => $f->status,
                ])->values(),
            ]);

        // All meeting IDs for this dept, current month
        $meetingIds = Meeting::where('type', 'department')
            ->where('department_id', $department->id)
            ->whereBetween('date', [$currentStart->toDateString(), $currentEnd->toDateString()])
            ->pluck('id');

        // Month totals
        $monthIncome  = MeetingFinance::whereIn('meeting_id', $meetingIds)->where('type', 'thu')->sum('amount');
        $monthExpense = MeetingFinance::whereIn('meeting_id', $meetingIds)->where('type', 'chi')->sum('amount');

        // Opening balance: all dept meetings BEFORE this month
        $allPrevMeetingIds = Meeting::where('type', 'department')
            ->where('department_id', $department->id)
            ->where('date', '<', $currentStart->toDateString())
            ->pluck('id');
        $prevIncome  = MeetingFinance::whereIn('meeting_id', $allPrevMeetingIds)->where('type', 'thu')->sum('amount');
        $prevExpense = MeetingFinance::whereIn('meeting_id', $allPrevMeetingIds)->where('type', 'chi')->sum('amount');
        $openingBalance = $prevIncome - $prevExpense;
        $closingBalance = $openingBalance + $monthIncome - $monthExpense;

        // Funds for this department
        $funds = DepartmentFund::where('department_id', $department->id)->get()
            ->map(fn($f) => ['id' => $f->id, 'name' => $f->name, 'balance' => $f->balance]);

        // Available departments for switcher
        $availableDepts = $this->getAvailableDepartments($request->user());

        return Inertia::render('Portal/Finance/Index', [
            'department'           => $department,
            'availableDepartments' => $availableDepts,
            'isGlobalAdmin'        => $request->user()->isSuperAdmin(),
            'canManage'            => $request->user()->isSuperAdmin(),
            'meetings'             => $meetings,
            'funds'                => $funds,
            'filters'              => ['month' => $month, 'year' => $year],
            'summary' => [
                'month_income'    => $monthIncome,
                'month_expense'   => $monthExpense,
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'meeting_count'   => $meetings->count(),
            ],
        ]);
    }

    // ============================================================
    // STORE FINANCE for a meeting (tiền dâng / chi)
    // ============================================================
    public function storeFinance(Request $request, Meeting $meeting)
    {
        $department = $this->getActiveDepartment();
        $this->authorizeForDept($department, $request);

        if ($meeting->department_id !== $department->id || $meeting->type !== 'department') {
            abort(403, 'Buổi nhóm không thuộc ban ngành này.');
        }

        $data = $request->validate([
            'finances'              => 'required|array|min:1',
            'finances.*.type'       => 'required|in:thu,chi',
            'finances.*.amount'     => 'required|numeric|min:0',
            'finances.*.category'   => 'nullable|string|max:255',
        ]);

        // Delete existing then re-insert (replace strategy)
        MeetingFinance::where('meeting_id', $meeting->id)->delete();

        foreach ($data['finances'] as $item) {
            if (($item['amount'] ?? 0) > 0) {
                MeetingFinance::create([
                    'meeting_id' => $meeting->id,
                    'type'       => $item['type'],
                    'amount'     => $item['amount'],
                    'category'   => $item['category'] ?? null,
                    'status'     => 'approved',
                ]);
            }
        }

        return redirect()->back()->with('message', 'Đã cập nhật tài chính buổi nhóm.');
    }

    // ============================================================
    // DELETE FINANCE for a meeting (xóa tất cả thu/chi)
    // ============================================================
    public function deleteFinance(Meeting $meeting)
    {
        $department = $this->getActiveDepartment();
        if ($meeting->department_id !== $department->id || $meeting->type !== 'department') {
            abort(403);
        }
        MeetingFinance::where('meeting_id', $meeting->id)->delete();
        return redirect()->back()->with('message', 'Đã xóa tài chính buổi nhóm.');
    }

    // ============================================================
    // FUND management (kept for compatibility)
    // ============================================================
    public function storeFund(Request $request)
    {
        $department = $this->getActiveDepartment();
        $this->authorizeForDept($department, $request);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        DepartmentFund::create(array_merge($data, ['department_id' => $department->id]));

        return redirect()->back()->with('message', 'Tạo quỹ thành công!');
    }

    public function destroyFund(DepartmentFund $fund)
    {
        $department = $this->getActiveDepartment();
        if ($fund->department_id !== $department->id) abort(403);
        if ($fund->balance != 0) {
            return redirect()->back()->with('error', 'Không thể xóa quỹ còn số dư.');
        }
        $fund->delete();
        return redirect()->back()->with('message', 'Đã xóa quỹ.');
    }

    // ============================================================
    // HELPERS
    // ============================================================
    private function getAvailableDepartments($user): \Illuminate\Support\Collection
    {
        return app(\App\Services\PortalService::class)->getAvailableDepartments($user, 'activities');
    }
}
