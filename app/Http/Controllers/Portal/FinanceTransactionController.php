<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FinanceTransaction;
use App\Models\FinanceFund;
use App\Models\MemberContribution;
use App\Models\Department;
use App\Models\FinanceSessionMetric;
use App\Models\FundTransfer;
use App\Services\PortalService;

class FinanceTransactionController extends Controller
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

        // Determine which funds to query
        $fundsQuery = FinanceFund::query();
        if ($activeDeptId) {
            $fundsQuery->where('owner_type', 'department')->where('owner_id', $activeDeptId);
        } elseif ($isGlobalAdmin) {
            $fundsQuery->where('owner_type', 'church');
        }

        $funds = $fundsQuery->get();
        $fundIds = $funds->pluck('id');

        $transactions = FinanceTransaction::with(['fund', 'sessionMetric', 'contributions'])
            ->whereIn('fund_id', $fundIds)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Transfers in the period
        $transfers = FundTransfer::with(['fromFund', 'toFund'])
            ->where(function($q) use ($fundIds) {
                $q->whereIn('from_fund_id', $fundIds)->orWhereIn('to_fund_id', $fundIds);
            })
            ->whereYear('transfer_date', $year)
            ->whereMonth('transfer_date', $month)
            ->orderBy('transfer_date', 'desc')
            ->get();

        // Calculate totals for the period
        $periodIncome = FinanceTransaction::whereIn('fund_id', $fundIds)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->where('status', 'approved')
            ->where('type', 'income')
            ->sum('amount');
            
        $periodExpense = FinanceTransaction::whereIn('fund_id', $fundIds)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->where('status', 'approved')
            ->where('type', 'expense')
            ->sum('amount');

        // Cumulative balance
        $endOfMonth = \Carbon\Carbon::create($year, $month)->endOfMonth();
        $totalIncome = FinanceTransaction::whereIn('fund_id', $fundIds)
            ->where('transaction_date', '<=', $endOfMonth)
            ->where('status', 'approved')
            ->where('type', 'income')
            ->sum('amount');
            
        $totalExpense = FinanceTransaction::whereIn('fund_id', $fundIds)
            ->where('transaction_date', '<=', $endOfMonth)
            ->where('status', 'approved')
            ->where('type', 'expense')
            ->sum('amount');

        $totalTransfersOut = FundTransfer::whereIn('from_fund_id', $fundIds)
            ->where('transfer_date', '<=', $endOfMonth)
            ->where('status', 'approved')
            ->sum('amount');

        $totalTransfersIn = FundTransfer::whereIn('to_fund_id', $fundIds)
            ->where('transfer_date', '<=', $endOfMonth)
            ->where('status', 'approved')
            ->sum('amount');

        $currentBalance = $totalIncome - $totalExpense - $totalTransfersOut + $totalTransfersIn;

        // Fund balances for display
        $fundsWithBalance = $funds->map(function ($fund) {
            return [
                'id' => $fund->id,
                'name' => $fund->name,
                'description' => $fund->description,
                'balance' => $fund->balance,
            ];
        });

        return Inertia::render('Finance/Transactions/Index', [
            'transactions' => $transactions,
            'transfers' => $transfers,
            'funds' => $fundsWithBalance,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ],
            'summary' => [
                'periodIncome' => $periodIncome,
                'periodExpense' => $periodExpense,
                'currentBalance' => $currentBalance,
            ],
            'canManage' => app(PortalService::class)->canManage($request->user(), (int) session('active_portal_dept_id', session('active_ministry_dept_id', 0)), 'finance'),
            'canApprove' => $request->user()->isSuperAdmin(),
            'department' => $department,
            'isGlobalAdmin' => $isGlobalAdmin,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage('finance');


        $validated = $request->validate([
            'fund_id'          => 'required|exists:finance_funds,id',
            'amount'           => 'required|numeric|min:0',
            'type'             => 'required|in:income,expense',
            'category'         => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'description'      => 'nullable|string',
            'attendance_count' => 'nullable|integer|min:0',
            'tithe_count'      => 'nullable|integer|min:0',
            'metrics_notes'    => 'nullable|string',
            // contribution breakdown for tithe
            'contributions'    => 'nullable|array',
            'contributions.*.member_group' => 'required_with:contributions|string',
            'contributions.*.people_count' => 'required_with:contributions|integer|min:0',
            'contributions.*.amount'       => 'required_with:contributions|integer|min:0',
        ]);

        $fund = FinanceFund::findOrFail($validated['fund_id']);
        
        // Security: ensure user can transact in this fund
        $activeDeptId = session('active_finance_dept_id');
        $isGlobalAdmin = $request->user()->isSuperAdmin();
        
        if (!$isGlobalAdmin) {
            if ($fund->owner_type !== 'department' || $fund->owner_id != $activeDeptId) {
                abort(403, 'Bạn không thể lập phiếu cho quỹ không thuộc quyền quản lý.');
            }
        }

        \DB::transaction(function () use ($validated, $fund, $request, $activeDeptId) {
            $metricsId = null;
            
            if (!empty($validated['attendance_count']) || !empty($validated['tithe_count'])) {
                $deptId = $fund->owner_type === 'department' ? $fund->owner_id : $activeDeptId;
                $metric = FinanceSessionMetric::create([
                    'department_id'    => $deptId,
                    'period_date'      => $validated['transaction_date'],
                    'attendance_count' => $validated['attendance_count'] ?? null,
                    'tithe_count'      => $validated['tithe_count'] ?? null,
                    'notes'            => $validated['metrics_notes'] ?? null,
                ]);
                $metricsId = $metric->id;
            }

            $transaction = FinanceTransaction::create([
                'fund_id'           => $fund->id,
                'amount'            => $validated['amount'],
                'type'              => $validated['type'],
                'category'          => $validated['category'] ?? null,
                'transaction_date'  => $validated['transaction_date'],
                'description'       => $validated['description'] ?? null,
                'status'            => 'pending',
                'session_metrics_id' => $metricsId,
            ]);
            
            // Auto-approve for authorized users
            if ($request->user()->isSuperAdmin()) {
                $transaction->update(['status' => 'approved']);
            }

            // Store per-group contributions if provided
            if (!empty($validated['contributions'])) {
                foreach ($validated['contributions'] as $contrib) {
                    if ($contrib['people_count'] > 0 || $contrib['amount'] > 0) {
                        MemberContribution::create([
                            'transaction_id' => $transaction->id,
                            'member_group'   => $contrib['member_group'],
                            'people_count'   => $contrib['people_count'],
                            'amount'         => $contrib['amount'],
                        ]);
                    }
                }
            }
        });


        return back()->with('message', 'Đã tạo giao dịch thành công.');
    }

    public function update(Request $request, FinanceTransaction $transaction)
    {
        $this->authorizeManage('finance');


        $validated = $request->validate([
            'amount'           => 'required|numeric|min:0',
            'type'             => 'required|in:income,expense',
            'category'         => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'description'      => 'nullable|string',
            'attendance_count' => 'nullable|integer|min:0',
            'tithe_count'      => 'nullable|integer|min:0',
            'metrics_notes'    => 'nullable|string',
            'contributions'    => 'nullable|array',
            'contributions.*.member_group' => 'required_with:contributions|string',
            'contributions.*.people_count' => 'required_with:contributions|integer|min:0',
            'contributions.*.amount'       => 'required_with:contributions|integer|min:0',
        ]);

        \DB::transaction(function () use ($validated, $transaction) {
            $transaction->update([
                'amount'          => $validated['amount'],
                'type'            => $validated['type'],
                'category'        => $validated['category'] ?? null,
                'transaction_date' => $validated['transaction_date'],
                'description'     => $validated['description'] ?? null,
            ]);

            // Update or create session metric
            if (!empty($validated['attendance_count']) || !empty($validated['tithe_count'])) {
                if ($transaction->session_metrics_id) {
                    FinanceSessionMetric::where('id', $transaction->session_metrics_id)->update([
                        'attendance_count' => $validated['attendance_count'] ?? null,
                        'tithe_count'      => $validated['tithe_count'] ?? null,
                        'notes'            => $validated['metrics_notes'] ?? null,
                        'period_date'      => $validated['transaction_date'],
                    ]);
                } else {
                    $fund = $transaction->fund;
                    $deptId = $fund->owner_type === 'department' ? $fund->owner_id : null;
                    $metric = FinanceSessionMetric::create([
                        'department_id'    => $deptId,
                        'period_date'      => $validated['transaction_date'],
                        'attendance_count' => $validated['attendance_count'] ?? null,
                        'tithe_count'      => $validated['tithe_count'] ?? null,
                        'notes'            => $validated['metrics_notes'] ?? null,
                    ]);
                    $transaction->update(['session_metrics_id' => $metric->id]);
                }
            }

            // Update contributions: delete old and re-create
            if (isset($validated['contributions'])) {
                $transaction->contributions()->delete();
                foreach ($validated['contributions'] as $contrib) {
                    if ($contrib['people_count'] > 0 || $contrib['amount'] > 0) {
                        MemberContribution::create([
                            'transaction_id' => $transaction->id,
                            'member_group'   => $contrib['member_group'],
                            'people_count'   => $contrib['people_count'],
                            'amount'         => $contrib['amount'],
                        ]);
                    }
                }
            }
        });

        return back()->with('message', 'Đã cập nhật giao dịch.');
    }

    public function destroy(FinanceTransaction $transaction)
    {
        $this->authorizeManage('finance');

        
        $metricId = $transaction->session_metrics_id;
        
        $transaction->contributions()->delete();
        $transaction->delete();
        
        if ($metricId) {
            $inUse = FinanceTransaction::where('session_metrics_id', $metricId)->exists();
            if (!$inUse) {
                FinanceSessionMetric::where('id', $metricId)->delete();
            }
        }

        return back()->with('message', 'Đã xóa giao dịch.');
    }

    public function approve(Request $request, FinanceTransaction $transaction)
    {
        $this->authorizeManage('finance');


        $request->validate([
            'status' => 'required|in:pending,approved'
        ]);

        $transaction->update(['status' => $request->status]);

        return back()->with('message', 'Trạng thái giao dịch đã được cập nhật.');
    }
}
