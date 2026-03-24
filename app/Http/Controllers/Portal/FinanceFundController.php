<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceFund;

class FinanceFundController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeFeature('finance');


        $user = $request->user();
        $isGlobalAdmin = $user->isSuperAdmin();
        $activeDeptId  = session('active_finance_dept_id');

        $fundsQuery = FinanceFund::query();
        if ($activeDeptId) {
            $fundsQuery->where('owner_type', 'department')->where('owner_id', $activeDeptId);
        } elseif ($isGlobalAdmin) {
            $fundsQuery->where('owner_type', 'church');
        }

        $funds = $fundsQuery->get()->map(fn($f) => [
            'id'          => $f->id,
            'name'        => $f->name,
            'description' => $f->description,
            'balance'     => $f->balance,
        ]);

        return inertia('Finance/Funds/Index', [
            'funds'         => $funds,
            'isGlobalAdmin' => $isGlobalAdmin,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeManage('finance');


        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $activeDeptId = session('active_finance_dept_id');
        $isGlobalAdmin = $request->user()->isSuperAdmin();

        // Default to department fund if not a global admin, otherwise create church fund or assign to dept
        $ownerType = 'department';
        $ownerId = $activeDeptId;

        if ($isGlobalAdmin && !$activeDeptId) {
            $ownerType = 'church';
            $ownerId = null;
        }

        FinanceFund::create([
            'name' => $validated['name'],
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
        ]);

        return back()->with('message', 'Đã tạo quỹ mới.');
    }

    public function update(Request $request, FinanceFund $fund)
    {
        $this->authorizeManage('finance');


        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $fund->update($validated);

        return back()->with('message', 'Đã cập nhật quỹ.');
    }

    public function destroy(FinanceFund $fund)
    {
        $this->authorizeManage('finance');


        if ($fund->transactions()->count() > 0) {
            return back()->with('error', 'Không thể xóa quỹ đang có giao dịch.');
        }

        $fund->delete();

        return back()->with('message', 'Đã xóa quỹ.');
    }
}
