<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fund;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        // 1. Lấy danh sách giao dịch dâng hiến
        $query = Donation::with(['fund', 'user', 'recorder'])->latest('donation_date')->latest('id');

        if ($request->has('search') && $request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('member_code', 'like', "%{$request->search}%");
            })->orWhere('reference_number', 'like', "%{$request->search}%")
              ->orWhere('notes', 'like', "%{$request->search}%");
        }

        if ($request->has('fund_id') && $request->fund_id) {
            $query->where('fund_id', $request->fund_id);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $donations = $query->paginate(20)->withQueryString();

        // 2. Lấy thông tin các Quỹ (Funds)
        $funds = Fund::withCount('donations')->get();
        // Cập nhật lại balance thực tế nếu cần thiết (hoặc trigger / observer đã lo việc này)
        
        // 3. Thống kê nhanh trong tháng này
        $currentMonthTotal = Donation::whereMonth('donation_date', date('m'))
                                     ->whereYear('donation_date', date('Y'))
                                     ->sum('amount');
                                     
        $titheMonthTotal = Donation::where('type', 'tithe')
                                   ->whereMonth('donation_date', date('m'))
                                   ->whereYear('donation_date', date('Y'))
                                   ->sum('amount');

        return Inertia::render('Admin/Finance/Donations/Index', [
            'donations' => $donations,
            'funds' => $funds,
            'filters' => $request->only(['search', 'fund_id', 'type']),
            'stats' => [
                'current_month_total' => $currentMonthTotal,
                'tithe_month_total' => $titheMonthTotal
            ]
        ]);
    }

    public function createBatch()
    {
        $funds = Fund::where('is_active', true)->get(['id', 'name', 'type']);
        return Inertia::render('Admin/Finance/Donations/BatchEntry', [
            'funds' => $funds
        ]);
    }

    // API phục vụ cho Autocomplete User
    public function searchUsers(Request $request)
    {
        $search = $request->query('q');
        if (!$search) return response()->json([]);

        $users = User::where('name', 'like', "%{$search}%")
            ->orWhere('member_code', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%")
            ->limit(10)
            ->get(['id', 'name', 'email', 'member_code', 'phone']);
            
        return response()->json($users);
    }

    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'entries' => 'required|array|min:1',
            'entries.*.fund_id' => 'required|exists:funds,id',
            'entries.*.user_id' => 'nullable|exists:users,id',
            'entries.*.type' => 'required|in:tithe,offering,thanksgiving,pledge,special',
            'entries.*.amount' => 'required|numeric|min:1000',
            'entries.*.donation_date' => 'required|date',
            'entries.*.payment_method' => 'required|in:cash,transfer,card',
            'entries.*.reference_number' => 'nullable|string',
            'entries.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['entries'] as $entry) {
                // 1. Tạo Record Donation
                $entry['recorded_by'] = Auth::id();
                $donation = Donation::create($entry);

                // 2. Cập nhật Balance của Fund
                $fund = Fund::find($entry['fund_id']);
                $fund->balance += $entry['amount'];
                $fund->save();
            }
        });

        return redirect()->route('admin.donations.index')->with('success', 'Đã lưu thành công ' . count($validated['entries']) . ' khoản dâng hiến vào hệ thống.');
    }

    // --- Quản lý Danh mục Quỹ (Funds) ---
    public function storeFund(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:funds|regex:/^[A-Z0-9_\-]+$/|max:50',
            'description' => 'nullable|string',
            'type' => 'required|in:general,building,mission,charity,other',
            'is_active' => 'boolean'
        ]);

        Fund::create($validated);
        return redirect()->back()->with('success', 'Đã tạo quỹ mới.');
    }

    public function updateFund(Request $request, Fund $fund)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|regex:/^[A-Z0-9_\-]+$/|max:50|unique:funds,code,'.$fund->id,
            'description' => 'nullable|string',
            'type' => 'required|in:general,building,mission,charity,other',
            'is_active' => 'boolean'
        ]);

        $fund->update($validated);
        return redirect()->back()->with('success', 'Đã cập nhật quỹ.');
    }
}
