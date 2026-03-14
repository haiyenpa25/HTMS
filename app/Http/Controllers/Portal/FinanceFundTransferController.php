<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\FundTransfer;
use App\Models\FinanceFund;
use App\Models\Department;
use Carbon\Carbon;

class FinanceFundTransferController extends Controller
{
    public function store(Request $request)
    {
        Gate::authorize('transfer', FundTransfer::class);

        $validated = $request->validate([
            'from_fund_id' => 'required|exists:finance_funds,id',
            'to_fund_id'   => 'required|exists:finance_funds,id|different:from_fund_id',
            'amount'       => 'required|numeric|min:1',
            'note'         => 'nullable|string|max:500',
            'transfer_date' => 'required|date',
        ]);

        $fromFund = FinanceFund::findOrFail($validated['from_fund_id']);
        $toFund   = FinanceFund::findOrFail($validated['to_fund_id']);

        // Check if from_fund has enough balance
        if ($fromFund->balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Số dư quỹ nguồn không đủ để chuyển.']);
        }

        $transfer = FundTransfer::create([
            'from_fund_id'  => $validated['from_fund_id'],
            'to_fund_id'    => $validated['to_fund_id'],
            'amount'        => $validated['amount'],
            'note'          => $validated['note'] ?? null,
            'transfer_date' => $validated['transfer_date'],
            'status'        => 'pending',
            'created_by'    => $request->user()->id,
        ]);

        // If user can approve, auto-approve
        if ($request->user()->isSuperAdmin()) {
            $transfer->update(['status' => 'approved']);
        } else {
            $notifiers = \App\Models\User::permission('approve_finance')->get();
            if ($notifiers->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send($notifiers, new \App\Notifications\FinanceTransferNotification($transfer, $fromFund->name));
            }
        }

        return back()->with('message', 'Lệnh chuyển quỹ đã được tạo.');
    }

    public function approve(Request $request, FundTransfer $fundTransfer)
    {
        Gate::authorize('approve', $fundTransfer);

        $request->validate([
            'status' => 'required|in:pending,approved',
        ]);

        $fundTransfer->update(['status' => $request->status]);

        return back()->with('message', 'Trạng thái chuyển quỹ đã được cập nhật.');
    }

    public function destroy(FundTransfer $fundTransfer)
    {
        Gate::authorize('transfer', $fundTransfer);

        // Only allow deleting pending transfers
        if ($fundTransfer->status === 'approved') {
            return back()->withErrors(['error' => 'Không thể xóa lệnh chuyển quỹ đã được duyệt.']);
        }

        $fundTransfer->delete();

        return back()->with('message', 'Lệnh chuyển quỹ đã được xóa.');
    }
}
