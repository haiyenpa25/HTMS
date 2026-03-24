<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\FinanceFund;
use App\Models\FinanceTransaction;
use App\Models\MemberContribution;
use App\Models\FundTransfer;
use Inertia\Inertia;
use Carbon\Carbon;

class FinancePortalController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeFeature('finance');

        $user = $request->user();
        $isGlobalAdmin = $user->isSuperAdmin();

        // Get list of departments user can access
        $availableDepartments = collect();
        if ($isGlobalAdmin) {
            $availableDepartments = Department::select('id', 'name')->orderBy('name')->get();
        } else {
            $deptIds = $user->memberships()
                ->where('model_type', Department::class)
                ->pluck('model_id');
            $availableDepartments = Department::whereIn('id', $deptIds)->select('id', 'name')->get();
        }

        $activeDeptId = session('active_finance_dept_id');
        $activeDepartment = Department::find($activeDeptId);

        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));

        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd   = $currentStart->copy()->endOfMonth();
        $prevStart    = $currentStart->copy()->subMonth()->startOfMonth();
        $prevEnd      = $prevStart->copy()->endOfMonth();

        // Determine funds scope
        $fundsQuery = FinanceFund::query();
        if ($activeDeptId) {
            $fundsQuery->where('owner_type', 'department')->where('owner_id', $activeDeptId);
        } elseif ($isGlobalAdmin) {
            $fundsQuery->where('owner_type', 'church');
        }

        $funds    = $fundsQuery->get();
        $fundIds  = $funds->pluck('id');

        $getTotals = function ($start, $end) use ($fundIds, $activeDeptId) {
            $income = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$start, $end])
                ->where('status', 'approved')->where('type', 'income')->sum('amount');

            $expense = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$start, $end])
                ->where('status', 'approved')->where('type', 'expense')->sum('amount');

            $cumulativeIncome = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->where('transaction_date', '<=', $end)
                ->where('status', 'approved')->where('type', 'income')->sum('amount');

            $cumulativeExpense = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->where('transaction_date', '<=', $end)
                ->where('status', 'approved')->where('type', 'expense')->sum('amount');

            $balance = $cumulativeIncome - $cumulativeExpense;

            $metricQ = \App\Models\FinanceSessionMetric::whereBetween('period_date', [$start, $end]);
            $activeDeptId ? $metricQ->where('department_id', $activeDeptId) : $metricQ->whereNull('department_id');
            $attendance = $metricQ->sum('attendance_count');
            $tithes     = $metricQ->sum('tithe_count');

            return compact('income', 'expense', 'balance', 'attendance', 'tithes');
        };

        $currentTotals = $getTotals($currentStart, $currentEnd);
        $prevTotals    = $getTotals($prevStart, $prevEnd);

        $calcPercent = fn($c, $p) => $p == 0 ? ($c > 0 ? 100 : 0) : round((($c - $p) / $p) * 100, 1);

        $comparisons = [
            'income'     => $calcPercent($currentTotals['income'], $prevTotals['income']),
            'expense'    => $calcPercent($currentTotals['expense'], $prevTotals['expense']),
            'balance'    => $calcPercent($currentTotals['balance'], $prevTotals['balance']),
            'tithes'     => $calcPercent($currentTotals['tithes'], $prevTotals['tithes']),
            'attendance' => $calcPercent($currentTotals['attendance'], $prevTotals['attendance']),
        ];

        // Contribution breakdown by member_group for current period
        $txIds = FinanceTransaction::whereIn('fund_id', $fundIds)
            ->whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->where('type', 'income')
            ->where('status', 'approved')
            ->pluck('id');

        $contributionByGroup = MemberContribution::whereIn('transaction_id', $txIds)
            ->selectRaw('member_group, SUM(people_count) as total_people, SUM(amount) as total_amount')
            ->groupBy('member_group')
            ->orderBy('member_group')
            ->get();

        // Fund balances
        $fundsWithBalance = $funds->map(fn($f) => [
            'id'          => $f->id,
            'name'        => $f->name,
            'description' => $f->description,
            'balance'     => $f->balance,
        ]);

        return Inertia::render('Finance/Dashboard', [
            'activeDepartment'   => $activeDepartment,
            'availableDepartments' => $availableDepartments,
            'isGlobalAdmin'      => $isGlobalAdmin,
            'currentTotals'      => $currentTotals,
            'prevTotals'         => $prevTotals,
            'comparisons'        => $comparisons,
            'funds'              => $fundsWithBalance,
            'contributionByGroup' => $contributionByGroup,
            'filters'            => ['month' => $month, 'year' => $year],
        ]);
    }

    public function switchContext(Request $request)
    {
        $this->authorizeFeature('finance');

        $request->validate([
            'department_id' => 'required|exists:departments,id'
        ]);

        $user   = $request->user();
        $deptId = $request->department_id;

        if (!$user->isSuperAdmin()) {
            $hasAccess = $user->memberships()
                ->where('model_type', Department::class)
                ->where('model_id', $deptId)
                ->exists();

            if (!$hasAccess) {
                return back()->with('error', 'Bạn không có quyền quản lý tài chính ban ngành này.');
            }
        }

        session(['active_finance_dept_id' => $deptId]);

        return redirect()->route('finance.index');
    }
}
