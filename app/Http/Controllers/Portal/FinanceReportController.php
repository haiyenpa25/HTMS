<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FinanceTransaction;
use App\Models\FinanceFund;
use App\Models\Department;
use App\Models\FinanceSessionMetric;
use Carbon\Carbon;

class FinanceReportController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeFeature('finance');


        $user = $request->user();
        $isGlobalAdmin = $user->isSuperAdmin();

        
        $activeDeptId = session('active_finance_dept_id');
        $department = Department::find($activeDeptId);
        
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Current Period
        $currentStart = Carbon::create($year, $month, 1)->startOfMonth();
        $currentEnd = $currentStart->copy()->endOfMonth();

        // Previous Period for Comparison
        $prevStart = $currentStart->copy()->subMonth()->startOfMonth();
        $prevEnd = $prevStart->copy()->endOfMonth();

        // Determine funds to summarize
        $fundsQuery = FinanceFund::query();
        if (!$isGlobalAdmin && $activeDeptId) {
             $fundsQuery->where('owner_type', 'department')->where('owner_id', $activeDeptId);
        } elseif ($isGlobalAdmin && $activeDeptId) {
             $fundsQuery->where('owner_type', 'department')->where('owner_id', $activeDeptId);
        } elseif ($isGlobalAdmin && !$activeDeptId) {
             $fundsQuery->where('owner_type', 'church');
        }

        $funds = $fundsQuery->get();
        $fundIds = $funds->pluck('id');

        // General Totals helper
        $getTotals = function($start, $end) use ($fundIds, $activeDeptId) {
            $income = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$start, $end])
                ->where('status', 'approved')
                ->where('type', 'income')
                ->sum('amount');
                
            $expense = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->whereBetween('transaction_date', [$start, $end])
                ->where('status', 'approved')
                ->where('type', 'expense')
                ->sum('amount');
                
            $cumulativeIncome = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->where('transaction_date', '<=', $end)
                ->where('status', 'approved')
                ->where('type', 'income')
                ->sum('amount');
                
            $cumulativeExpense = FinanceTransaction::whereIn('fund_id', $fundIds)
                ->where('transaction_date', '<=', $end)
                ->where('status', 'approved')
                ->where('type', 'expense')
                ->sum('amount');

            $balance = $cumulativeIncome - $cumulativeExpense;
            
            // Get Metrics totals for period
            $metricsQuery = FinanceSessionMetric::whereBetween('period_date', [$start, $end]);
            if ($activeDeptId) {
                $metricsQuery->where('department_id', $activeDeptId);
            } else {
                $metricsQuery->whereNull('department_id');
            }
            
            $attendance = $metricsQuery->sum('attendance_count');
            $tithes = $metricsQuery->sum('tithe_count');

            return [
                'income' => $income,
                'expense' => $expense,
                'balance' => $balance,
                'attendance' => $attendance,
                'tithes' => $tithes
            ];
        };

        $currentTotals = $getTotals($currentStart, $currentEnd);
        $prevTotals = $getTotals($prevStart, $prevEnd);

        // Calculate Percentages
        $calcPercent = function($curr, $prev) {
            if ($prev == 0) return $curr > 0 ? 100 : 0;
            return round((($curr - $prev) / $prev) * 100, 1);
        };

        $comparisons = [
            'income' => $calcPercent($currentTotals['income'], $prevTotals['income']),
            'expense' => $calcPercent($currentTotals['expense'], $prevTotals['expense']),
            'balance' => $calcPercent($currentTotals['balance'], $prevTotals['balance']),
            'tithes' => $calcPercent($currentTotals['tithes'], $prevTotals['tithes']),
            'attendance' => $calcPercent($currentTotals['attendance'], $prevTotals['attendance']),
        ];

        // Upcoming Activities (if department context)
        $activities = collect();
        if ($activeDeptId) {
             // Mocking activities retrieval based on Visitation data or meetings
             // If this was a fully integrated module we'd pull from activities tables
             // For now we'll query visitations scheduled for this month
             $activities = \App\Models\Visitation::where('department_id', $activeDeptId)
                 ->whereBetween('visit_date', [$currentStart, $currentEnd])
                 ->with('members')
                 ->orderBy('visit_date')
                 ->get()
                 ->map(function ($visit) {
                     return [
                         'id' => 'v_'.$visit->id,
                         'title' => 'Thăm viếng: ' . $visit->members->pluck('full_name')->join(', '),
                         'date' => $visit->visit_date,
                         'type' => 'visitation'
                     ];
                 });
        }

        // Transactions for the Grid/Excel view
        $transactions = FinanceTransaction::with(['fund', 'sessionMetric'])
            ->whereIn('fund_id', $fundIds)
            ->whereBetween('transaction_date', [$currentStart, $currentEnd])
            ->where('status', 'approved')
            ->orderBy('transaction_date', 'asc')
            ->get();

        return Inertia::render('Finance/Reports/Index', [
            'currentTotals' => $currentTotals,
            'prevTotals' => $prevTotals,
            'comparisons' => $comparisons,
            'transactions' => $transactions,
            'activities' => $activities,
            'funds' => $funds,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ],
            'department' => $department,
            'isGlobalAdmin' => $isGlobalAdmin
        ]);
    }
}
