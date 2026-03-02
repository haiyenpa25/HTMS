<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Department;
use App\Models\DepartmentFund;
use App\Models\DepartmentMeeting;
use App\Models\DepartmentTransaction;
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
        if ($user->hasRole(['Super_Admin', 'Pastor'])) {
            return; // Full access
        }

        // Must have permission and belong to the department
        Gate::authorize('viewAny', DepartmentMeeting::class);

        $memberId = $user->member_id;
        if (!$memberId) {
            abort(403, 'Bạn không thuộc ban ngành này.');
        }
        $belongs = DB::table('org_memberships')
            ->where('model_type', Department::class)
            ->where('model_id', $department->id)
            ->where('member_id', $memberId)
            ->exists();

        if (!$belongs) {
            abort(403, 'Bạn không thuộc ban ngành này.');
        }
    }

    // ============================================================
    // INDEX — Main Finance Page
    // ============================================================
    public function index(Request $request)
    {
        Gate::authorize('viewAny', DepartmentMeeting::class);

        $department = $this->getActiveDepartment();
        $this->authorizeForDept($department, $request);

        $month = (int) $request->input('month', date('m'));
        $year  = (int) $request->input('year', date('Y'));

        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd   = $currentStart->copy()->endOfMonth();
        $prevStart    = $currentStart->copy()->subMonth()->startOfMonth();
        $prevEnd      = $prevStart->copy()->endOfMonth();

        // Funds for this department
        $funds = DepartmentFund::where('department_id', $department->id)->get();
        $fundIds = $funds->pluck('id');

        // Meetings in the month, eager-load their transactions
        $meetings = DepartmentMeeting::where('department_id', $department->id)
            ->whereBetween('meeting_date', [$currentStart, $currentEnd])
            ->orderBy('meeting_date', 'asc')
            ->with(['transactions' => fn($q) => $q->where('status', 'approved')])
            ->get()
            ->map(fn($m) => [
                'id'                   => $m->id,
                'meeting_date'         => $m->meeting_date->toDateString(),
                'attendance_morning'   => $m->attendance_morning,
                'attendance_afternoon' => $m->attendance_afternoon,
                'total_attendance'     => $m->total_attendance,
                'note'                 => $m->note,
                'session_income'       => $m->transactions->where('type', 'income')->sum('amount'),
                'session_expense'      => $m->transactions->where('type', 'expense')->sum('amount'),
                'session_balance'      => $m->transactions->where('type', 'income')->sum('amount')
                                        - $m->transactions->where('type', 'expense')->sum('amount'),
                'transactions'         => $m->transactions->values(),
            ]);

        // Month totals
        $monthIncome = DepartmentTransaction::whereIn('department_fund_id', $fundIds)
            ->whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->where('type', 'income')->where('status', 'approved')->sum('amount');

        $monthExpense = DepartmentTransaction::whereIn('department_fund_id', $fundIds)
            ->whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->where('type', 'expense')->where('status', 'approved')->sum('amount');

        // Balance brought forward from previous month (all approved up to end of prev month)
        $prevIncomeTotal = DepartmentTransaction::whereIn('department_fund_id', $fundIds)
            ->where('transaction_date', '<=', $prevEnd)
            ->where('type', 'income')->where('status', 'approved')->sum('amount');
        $prevExpenseTotal = DepartmentTransaction::whereIn('department_fund_id', $fundIds)
            ->where('transaction_date', '<=', $prevEnd)
            ->where('type', 'expense')->where('status', 'approved')->sum('amount');
        $openingBalance = $prevIncomeTotal - $prevExpenseTotal;

        // Current month closing balance
        $closingBalance = $openingBalance + $monthIncome - $monthExpense;

        // Attendance comparison
        $currentAvgAttendance = $meetings->avg('total_attendance') ?? 0;
        $prevMeetings = DepartmentMeeting::where('department_id', $department->id)
            ->whereBetween('meeting_date', [$prevStart, $prevEnd])
            ->get();
        $prevAvgAttendance = $prevMeetings->avg(fn($m) => max($m->attendance_morning, $m->attendance_afternoon)) ?? 0;
        $attendanceChange = $prevAvgAttendance > 0
            ? round((($currentAvgAttendance - $prevAvgAttendance) / $prevAvgAttendance) * 100, 1)
            : ($currentAvgAttendance > 0 ? 100 : 0);

        // Fund balances
        $fundsWithBalance = $funds->map(fn($f) => [
            'id'          => $f->id,
            'name'        => $f->name,
            'description' => $f->description,
            'balance'     => $f->balance,
        ]);

        // Available departments for switcher
        $availableDepts = $this->getAvailableDepartments($request->user());

        return Inertia::render('Portal/Finance/Index', [
            'department'         => $department,
            'availableDepartments' => $availableDepts,
            'isGlobalAdmin'       => $request->user()->hasRole(['Super_Admin', 'Pastor']),
            'canManage'           => $request->user()->hasPermissionTo('manage_dept_finance'),
            'meetings'            => $meetings,
            'funds'               => $fundsWithBalance,
            'filters'             => ['month' => $month, 'year' => $year],
            'summary' => [
                'month_income'       => $monthIncome,
                'month_expense'      => $monthExpense,
                'month_balance'      => $monthIncome - $monthExpense,
                'opening_balance'    => $openingBalance,
                'closing_balance'    => $closingBalance,
                'avg_attendance'     => round($currentAvgAttendance, 1),
                'prev_avg_attendance' => round($prevAvgAttendance, 1),
                'attendance_change'   => $attendanceChange,
                'meeting_count'       => $meetings->count(),
            ],
        ]);
    }

    // ============================================================
    // STORE MEETING (with linked transactions)
    // ============================================================
    public function storeMeeting(Request $request)
    {
        Gate::authorize('manageMeeting', DepartmentMeeting::class);

        $department = $this->getActiveDepartment();
        $this->authorizeForDept($department, $request);

        $validated = $request->validate([
            'meeting_date'         => 'required|date',
            'attendance_morning'   => 'required|integer|min:0',
            'attendance_afternoon' => 'required|integer|min:0',
            'note'                 => 'nullable|string|max:500',
            // Linked transactions
            'transactions'         => 'nullable|array',
            'transactions.*.department_fund_id' => 'required_with:transactions|exists:department_funds,id',
            'transactions.*.type'               => 'required_with:transactions|in:income,expense',
            'transactions.*.amount'             => 'required_with:transactions|integer|min:0',
            'transactions.*.category'           => 'nullable|string|max:255',
            'transactions.*.description'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $department, $request) {
            $meeting = DepartmentMeeting::create([
                'department_id'        => $department->id,
                'meeting_date'         => $validated['meeting_date'],
                'attendance_morning'   => $validated['attendance_morning'],
                'attendance_afternoon' => $validated['attendance_afternoon'],
                'note'                 => $validated['note'] ?? null,
            ]);

            foreach ($validated['transactions'] ?? [] as $tx) {
                if (($tx['amount'] ?? 0) <= 0) continue;
                DepartmentTransaction::create([
                    'department_fund_id'   => $tx['department_fund_id'],
                    'department_meeting_id' => $meeting->id,
                    'type'                 => $tx['type'],
                    'amount'               => $tx['amount'],
                    'category'             => $tx['category'] ?? null,
                    'description'          => $tx['description'] ?? null,
                    'transaction_date'     => $validated['meeting_date'],
                    'status'               => $request->user()->hasPermissionTo('manage_dept_finance') ? 'approved' : 'pending',
                ]);
            }
        });

        return back()->with('message', 'Buổi nhóm đã được lưu thành công.');
    }

    // ============================================================
    // UPDATE MEETING
    // ============================================================
    public function updateMeeting(Request $request, DepartmentMeeting $meeting)
    {
        Gate::authorize('manageMeeting', $meeting);

        $department = $this->getActiveDepartment();
        $this->authorizeForDept($department, $request);

        $validated = $request->validate([
            'meeting_date'         => 'required|date',
            'attendance_morning'   => 'required|integer|min:0',
            'attendance_afternoon' => 'required|integer|min:0',
            'note'                 => 'nullable|string|max:500',
            'transactions'         => 'nullable|array',
            'transactions.*.id'                 => 'nullable|exists:department_transactions,id',
            'transactions.*.department_fund_id' => 'required_with:transactions|exists:department_funds,id',
            'transactions.*.type'               => 'required_with:transactions|in:income,expense',
            'transactions.*.amount'             => 'required_with:transactions|integer|min:0',
            'transactions.*.category'           => 'nullable|string|max:255',
            'transactions.*.description'        => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $meeting, $request) {
            $meeting->update([
                'meeting_date'         => $validated['meeting_date'],
                'attendance_morning'   => $validated['attendance_morning'],
                'attendance_afternoon' => $validated['attendance_afternoon'],
                'note'                 => $validated['note'] ?? null,
            ]);

            // Re-create transactions: delete existing linked ones and recreate
            $meeting->transactions()->delete();
            foreach ($validated['transactions'] ?? [] as $tx) {
                if (($tx['amount'] ?? 0) <= 0) continue;
                DepartmentTransaction::create([
                    'department_fund_id'    => $tx['department_fund_id'],
                    'department_meeting_id' => $meeting->id,
                    'type'                  => $tx['type'],
                    'amount'                => $tx['amount'],
                    'category'              => $tx['category'] ?? null,
                    'description'           => $tx['description'] ?? null,
                    'transaction_date'      => $validated['meeting_date'],
                    'status'                => $request->user()->hasPermissionTo('manage_dept_finance') ? 'approved' : 'pending',
                ]);
            }
        });

        return back()->with('message', 'Buổi nhóm đã được cập nhật.');
    }

    // ============================================================
    // DELETE MEETING
    // ============================================================
    public function destroyMeeting(DepartmentMeeting $meeting)
    {
        Gate::authorize('manageMeeting', $meeting);
        $meeting->transactions()->delete();
        $meeting->delete();
        return back()->with('message', 'Đã xóa buổi nhóm.');
    }

    // ============================================================
    // STORE STANDALONE TRANSACTION
    // ============================================================
    public function storeTransaction(Request $request)
    {
        Gate::authorize('create', DepartmentTransaction::class);

        $department = $this->getActiveDepartment();
        $this->authorizeForDept($department, $request);

        $validated = $request->validate([
            'department_fund_id' => 'required|exists:department_funds,id',
            'type'               => 'required|in:income,expense',
            'amount'             => 'required|integer|min:1',
            'category'           => 'nullable|string|max:255',
            'description'        => 'nullable|string',
            'transaction_date'   => 'required|date',
        ]);

        DepartmentTransaction::create(array_merge($validated, [
            'status' => $request->user()->hasPermissionTo('manage_dept_finance') ? 'approved' : 'pending',
        ]));

        return back()->with('message', 'Giao dịch đã được lưu.');
    }

    // ============================================================
    // DELETE STANDALONE TRANSACTION
    // ============================================================
    public function destroyTransaction(DepartmentTransaction $transaction)
    {
        Gate::authorize('delete', $transaction);
        $transaction->delete();
        return back()->with('message', 'Đã xóa giao dịch.');
    }

    // ============================================================
    // FUND MANAGEMENT
    // ============================================================
    public function storeFund(Request $request)
    {
        Gate::authorize('create', DepartmentTransaction::class);

        $department = $this->getActiveDepartment();
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        DepartmentFund::create([
            'department_id' => $department->id,
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
        ]);

        return back()->with('message', 'Quỹ đã được tạo.');
    }

    public function destroyFund(DepartmentFund $fund)
    {
        Gate::authorize('delete', DepartmentTransaction::class);
        $fund->delete();
        return back()->with('message', 'Đã xóa quỹ.');
    }

    // ============================================================
    // PRIVATE HELPERS
    // ============================================================
    private function getAvailableDepartments($user): \Illuminate\Support\Collection
    {
        if ($user->hasRole(['Super_Admin', 'Pastor'])) {
            return Department::where('block', 'activities')->select('id', 'name')->get();
        }
        $memberId = $user->member_id;
        if (!$memberId) return collect();
        $deptIds = DB::table('org_memberships')
            ->where('model_type', Department::class)
            ->where('member_id', $memberId)
            ->pluck('model_id');
        return Department::whereIn('id', $deptIds)->where('block', 'activities')->select('id', 'name')->get();
    }
}
